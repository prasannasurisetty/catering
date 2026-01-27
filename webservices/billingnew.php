<?php

include('config.php');
include('dblayer.php');
session_start();

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);
$load = $data["load"] ?? "";
// $orderdate = $data["orderdate"] ?? "";
// $ordertime = $data["ordertime"] ?? "";
$customerid   = $data['customerid'] ?? null;
$addressid    = $data['addressid'] ?? null;
$refundorderdate = $data['refundorderdate'] ?? "";
$refundordertime = $data['refundordertime'] ?? "";
$refunddate =  $data['refunddate'] ?? "";
$refundamount = $data['refundamount'] ?? "";
$refundpaymode = $data['refundpaymode'] ?? "";
$orderdate = $data['orderdate'] ?? "";
$ordertime = $data['ordertime'] ?? "";
$customerId = $data['customerId'] ?? null;
$addressId = $data['addressId'] ?? null;

// // $foodtypeid = $data["foodtypeid"] ?? "";
// $cid = $data["cid"] ?? "";
// $aid = $data["aid"] ?? "";
// $fromdate = $data["fromdate"] ?? "";
// $houseno = $data["houseno"] ?? "";
// $link = $data["link"] ?? "";
// $street = $data["street"] ?? "";
// $area = $data["area"] ?? "";
// $phone = $data["phone"] ?? "";
// $landmark = $data["landmark"] ?? "";
// $pincode = $data["pincode"] ?? "";
// $address_id = $data["address_id"] ?? "";
// $default = !empty($data['default']) ? 1 : 0;




if ($load == "loadpaymode") {
    loadpaymode($conn);
} elseif ($load == "load_deliveryboys") {
    load_deliveryboys($conn, $data);
} else if ($load == "allorders") {
    allorders($conn);
} else if ($load == "savepayment") {
    savepayment($conn);
} else if ($load == "refund") {
    refund($conn);
}


function refund($conn)
{
    global $customerId, $addressId,
           $refunddate, $refundamount, $refundpaymode,
           $refundorderdate, $refundordertime;

    $adminid = $_SESSION['adminid'] ?? 0;

    $insert = "
        INSERT INTO catering_refund
        (customer_id, address_id, refund_date, refund_amount, pay_mode, admin_id)
        VALUES
        ('$customerId','$addressId','$refunddate','$refundamount','$refundpaymode','$adminid')
    ";

    $resultinsert = setData($conn, $insert);

    if (!$resultinsert) {
        echo json_encode([
            'code' => 400,
            'status' => 'failed',
            'message' => 'Failed to insert refund'
        ]);
        return;
    }

    $refund_id = mysqli_insert_id($conn);
    

    if (!$refund_id) {
        echo json_encode([
            'code' => 400,
            'status' => 'failed',
            'message' => 'Refund ID not generated'
        ]);
        return;
    }

    $update = "
        UPDATE catering_orders
        SET refund_id = '$refund_id',
            refund_status = 1
        WHERE customer_id = '$customerId'
          AND address_id  = '$addressId'
          AND order_date  = '$refundorderdate'
          AND order_time  = '$refundordertime'
          AND order_status = 0
        LIMIT 1
    ";

    $resultupdate = setData($conn, $update);

    if ($resultupdate) {
        echo json_encode([
            'code' => 200,
            'status' => 'success',
            'refund_id' => $refund_id
        ]);
    } else {
        echo json_encode([
            'code' => 400,
            'status' => 'failed',
            'message' => 'Refund saved but order update failed'
        ]);
    }
}




function savepayment($conn)
{
    header('Content-Type: application/json');



    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid JSON input"
        ]);
        return;
    }

    /* ===============================
       1️⃣ READ INPUTS
    =============================== */

    $customerid   = $data['customerid'] ?? '';
    $addressid    = $data['addressid'] ?? '';
    $orderdate    = $data['orderdate'] ?? '';
    $ordertime    = $data['ordertime'] ?? '';
    $paidamount   = floatval($data['paidamount'] ?? 0);
    $recoveryamt  = floatval($data['recoveryamt'] ?? 0);
    $paymode      = $data['paymode'] ?? '';
    $paydate      = $data['paydate'] ?? '';
    $adminid      = $_SESSION['adminid'] ?? 0;

    if (
        !$customerid || !$addressid || !$orderdate || !$ordertime ||
        !$paymode || !$paydate || $paidamount <= 0
    ) {
        echo json_encode([
            "status" => "failed",
            "message" => "Missing or invalid payment data"
        ]);
        return;
    }

    /* ===============================
       2️⃣ START TRANSACTION
    =============================== */

    mysqli_begin_transaction($conn);

    /* ===============================
       3️⃣ INSERT PAYMENT
    =============================== */

    $insertPaymentSql = "
        INSERT INTO catering_payments
        (customer_id, address_id, paid_date, paid_amount, pay_mode, admin_id)
        VALUES
        ('$customerid', '$addressid', '$paydate', '$paidamount', '$paymode', '$adminid')
    ";

    if (!mysqli_query($conn, $insertPaymentSql)) {
        mysqli_rollback($conn);
        echo json_encode([
            "status" => "failed",
            "message" => "Payment insert failed"
        ]);
        return;
    }

    /* ===============================
       4️⃣ FETCH ORDER
    =============================== */

    $orderSql = "
        SELECT order_id, grand_total, IFNULL(paid_amount,0) AS paid_amount
        FROM catering_orders
        WHERE customer_id = '$customerid'
          AND address_id  = '$addressid'
          AND order_date  = '$orderdate'
          AND order_time  = '$ordertime'
        LIMIT 1
    ";

    $orderRes = mysqli_query($conn, $orderSql);

    if (!$orderRes || mysqli_num_rows($orderRes) === 0) {
        mysqli_rollback($conn);
        echo json_encode([
            "status" => "failed",
            "message" => "Order not found"
        ]);
        return;
    }

    $order = mysqli_fetch_assoc($orderRes);

    /* ===============================
       5️⃣ CALCULATE PAYMENT STATUS
    =============================== */

    $previousPaid = floatval($order['paid_amount']);
    $grandTotal   = floatval($order['grand_total']);

    $newPaidAmount = $previousPaid + $paidamount;
    $totalPayable  = $grandTotal + $recoveryamt;

    $paymentStatus = ($newPaidAmount >= $totalPayable) ? 1 : 0;

    /* ===============================
       6️⃣ GET BILLING ID
    =============================== */

    $billingSql = "
        SELECT MAX(billing_id) AS billing_id
        FROM catering_payments
        WHERE customer_id = '$customerid'
          AND address_id  = '$addressid'
    ";

    $billingRes = mysqli_query($conn, $billingSql);
    $billingRow = mysqli_fetch_assoc($billingRes);
    $billingId  = $billingRow['billing_id'];

    /* ===============================
       7️⃣ UPDATE ORDER
    =============================== */

    $updateOrderSql = "
        UPDATE catering_orders
        SET
            paid_amount     = '$newPaidAmount',
            billing_id      = '$billingId',
            payment_status  = '$paymentStatus',
            recovery_amount = '$recoveryamt'
        WHERE order_id = '{$order['order_id']}'
    ";

    if (!mysqli_query($conn, $updateOrderSql)) {
        mysqli_rollback($conn);
        echo json_encode([
            "status" => "failed",
            "message" => "Order update failed"
        ]);
        return;
    }

    /* ===============================
       8️⃣ GENERATE RECEIPT (ONLY IF FULLY PAID)
    =============================== */

    if ($paymentStatus == 1) {

        $checkReceiptSql = "
            SELECT receipt_id
            FROM catering_receipts
            WHERE billing_id = '$billingId'
            LIMIT 1
        ";

        $checkRes = mysqli_query($conn, $checkReceiptSql);

        if ($checkRes && mysqli_num_rows($checkRes) == 0) {

            $insertReceiptSql = "
                INSERT INTO catering_receipts
                (billing_id, customer_id, address_id, receipt_date)
                VALUES
                ('$billingId', '$customerid', '$addressid', CURDATE())
            ";

            if (!mysqli_query($conn, $insertReceiptSql)) {
                mysqli_rollback($conn);
                echo json_encode([
                    "status" => "failed",
                    "message" => "Receipt generation failed"
                ]);
                return;
            }
        }
    }

    /* ===============================
       9️⃣ COMMIT
    =============================== */

    mysqli_commit($conn);

    echo json_encode([
        "status" => "success",
        "message" => "Payment saved successfully",
        "paid_amount"     => $newPaidAmount,
        "grand_total"     => $grandTotal,
        "recovery_amount" => $recoveryamt,
        "total_payable"   => $totalPayable,
        "payment_status"  => $paymentStatus,
        "billing_id"      => $billingId
    ]);
}




function allorders($conn)
{
    global $customerid, $addressid;
    $sql = "SELECT * FROM `catering_orders` WHERE customer_id = '$customerid' AND address_id = '$addressid' AND payment_status = '0' AND refund_status = '0'
    ORDER BY order_date DESC;";
    $result = getData($conn, $sql);

    if (!empty($result)) {
        echo json_encode([
            "code" => 200,
            "status" => "success",
            "data" => $result
        ]);
    } else {
        echo json_encode([
            "code" => 404,
            "status" => "No Records Found",
            "data" => []
        ]);
    }
}





function load_deliveryboys($conn)
{

    $data = [];

    $query = "SELECT di.ID,di.Name FROM deliveryinfo di";
    $result = getData($conn, $query);

    if ($result) {

        echo json_encode(["code" => "200", "status" => "Success", "data" => $result]);
    } else {
        echo json_encode(["code" => "500", "status" => "Error", "message" => mysqli_error($conn)]);
    }
}




function loadpaymode($conn)
{
    $selectsql = "SELECT * FROM `paymode`";
    $resultsql = getData($conn, $selectsql);
    if (count($resultsql) > 0) {
        $jsonresponse = array('code' => '200', 'status' => 'success', 'data' => $resultsql);
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '200', 'status' => 'No Records Found');
        echo json_encode($jsonresponse);
    }
}
