<?php

include('config.php');
include('dblayer.php');
session_start();

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);
$load = $data["load"] ?? "";
$orderdate = $data['orderdate'] ?? "";
$ordertime = $data['ordertime'] ?? "";
$customerid = $data['customerid'] ?? "";
$addressid = $data['addressid'] ?? "";
$paidamount = $data['paidamount'] ?? "";
$paymode = $data['paymode'] ?? "";
$paydate = $data['paydate'] ?? "";

if ($load == "savepayment") {
    savepayment($conn);
} else if ($load == "paymenthistory") {
    paymenthistory($conn);
} else if ($load == "fetchtotalamount") {
    fetchtotalamount($conn);
} else if ($load == "addutensils") {
    addtensils($conn);
} else if ($load == "fetchutensils") {
    fetchutensils($conn);
}


function fetchutensils($conn)
{
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !$data ||
        empty($data['customerid']) ||
        empty($data['addressid']) ||
        empty($data['orderdate']) ||
        empty($data['ordertime'])
    ) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid input data"
        ]);
        return;
    }

    $customerid = (int)$data['customerid'];
    $addressid  = (int)$data['addressid'];
    $orderdate  = mysqli_real_escape_string($conn, $data['orderdate']);
    $ordertime  = mysqli_real_escape_string($conn, $data['ordertime']);

    // 🔹 STEP 1: GET BILLING ID
    $utensilsSql = "
        SELECT utensils_id
        FROM catering_orders
        WHERE
            customer_id = $customerid
            AND address_id = $addressid
            AND order_date = '$orderdate'
            AND order_time = '$ordertime'
        LIMIT 1
    ";

    $utensilsRes = mysqli_query($conn, $utensilsSql);

    if (!$utensilsRes || mysqli_num_rows($utensilsRes) === 0) {
        echo json_encode([
            "status" => "success",
            "utensils_id" => null,
            "data" => []
        ]);
        return;
    }

    $utensilsRow = mysqli_fetch_assoc($utensilsRes);
    $utensils_id = (int)$utensilsRow['utensils_id'];

    // 🔹 STEP 2: FETCH UTENSILS USING BILLING ID
    $utensilsSql = "
        SELECT utensils_name, issued_qty, returned_qty
        FROM catering_utensils
        WHERE utensils_id = $utensils_id
    ";

    $utRes = mysqli_query($conn, $utensilsSql);

    $rows = [];
    while ($row = mysqli_fetch_assoc($utRes)) {
        $rows[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "utensils_id" => $utensils_id,
        "data" => $rows
    ]);
}






function addtensils($conn)
{
    global $customerid, $addressid, $orderdate, $ordertime;
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !$data ||
        empty($data['customerid']) ||
        empty($data['addressid']) ||
        empty($data['utensils_id']) ||
        empty($data['utensils'])
    ) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid input data"
        ]);
        return;
    }

    $customer_id = (int)$data['customerid'];
    $address_id  = (int)$data['addressid'];
    $utensils_id = (int)$data['utensils_id'];
    $utensils    = $data['utensils'];

    foreach ($utensils as $item) {

        $name = mysqli_real_escape_string(
            $conn,
            trim($item['utensils_name'] ?? "")
        );

        $issued_qty   = (int)($item['issued_qty'] ?? 0);
        $returned_qty = (int)($item['returned_qty'] ?? 0);

        if ($name === "") {
            continue;
        }

        // 🔍 CHECK IF UTENSIL ALREADY EXISTS
        $checkSql = "
            SELECT sno FROM catering_utensils
            WHERE
                customer_id = $customer_id
                AND address_id = $address_id
                AND utensils_id = $utensils_id
                AND utensils_name = '$name'
            LIMIT 1
        ";

        $checkRes = mysqli_query($conn, $checkSql);

        if ($checkRes && mysqli_num_rows($checkRes) > 0) {

            // 🔄 UPDATE (issued + returned)
            $updateSql = "
                UPDATE catering_utensils
                SET
                    issued_qty = $issued_qty,
                    returned_qty = $returned_qty
                WHERE
                    customer_id = $customer_id
                    AND address_id = $address_id
                    AND utensils_id = $utensils_id
                    AND utensils_name = '$name'
            ";

            mysqli_query($conn, $updateSql);
        } else {

            // ➕ INSERT (first time issue)
            $insertSql = "
                INSERT INTO catering_utensils
                (customer_id, address_id, utensils_id, utensils_name, issued_qty, returned_qty)
                VALUES
                ($customer_id, $address_id, $utensils_id, '$name', $issued_qty, $returned_qty)
            ";

            mysqli_query($conn, $insertSql);
        }
    }

    // ================================
    // 🔹 SAVE / UPDATE UTENSILS ID IN ORDERS TABLE
    // ================================



    if ($orderdate && $ordertime) {

        // Check if order already exists
        $checkOrderSql = "
        SELECT order_id FROM catering_orders
        WHERE
            customer_id = $customer_id
            AND address_id = $address_id
            AND order_date = '$orderdate'
            AND order_time = '$ordertime'
        LIMIT 1
       ";

        $orderRes = mysqli_query($conn, $checkOrderSql);

        if ($orderRes && mysqli_num_rows($orderRes) > 0) {

            // 🔄 UPDATE utensils_id
            $updateOrderSql = "
            UPDATE catering_orders
            SET utensils_id = $utensils_id
            WHERE
                customer_id = $customer_id
                AND address_id = $address_id
                AND order_date = '$orderdate'
                AND order_time = '$ordertime'
        ";

            mysqli_query($conn, $updateOrderSql);
        } else {

            // ➕ INSERT new order row
            $insertOrderSql = "
            INSERT INTO catering_orders
            (customer_id, address_id, order_date, order_time, utensils_id)
            VALUES
            ($customer_id, $address_id, '$orderdate', '$ordertime', $utensils_id)
        ";

            mysqli_query($conn, $insertOrderSql);
        }
    }


    echo json_encode([
        "status" => "success",
        "message" => "Utensils saved successfully"
    ]);
}





function fetchtotalamount($conn)
{
    global $customerid, $addressid, $orderdate, $ordertime;
    $sql = "
        SELECT (grand_total - paid_amount) AS totalamount
        FROM catering_orders
        WHERE customer_id = '$customerid'
          AND address_id  = '$addressid'
          AND order_date  = '$orderdate'
          AND order_time  = '$ordertime'
    ";

    $result = getData($conn, $sql);

    if ($result && count($result) > 0) {
        echo json_encode([
            "status" => "success",
            "totalamount" => $result[0]['totalamount']
        ]);
    } else {
        echo json_encode([
            "status" => "failed",
            "message" => "failed to fetch"
        ]);
    }
}

function paymenthistory($conn)
{
    global $orderdate, $customerid, $addressid;
    $sql = "SELECT c.paid_date,c.paid_amount,p.type AS pay_mode FROM `catering_payments` c 
            JOIN paymode p ON p.sno = c.pay_mode
           WHERE c.customer_id = '$customerid' AND c.address_id = '$addressid' ";
    $result = getData($conn, $sql);
    if (count($result) > 0) {
        $jsonresponse = array('code' => '200', 'status' => 'success', "data" => $result);
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '400', 'status' => 'No records found');
        echo json_encode($jsonresponse);
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

    // ✅ Read inputs (your style)
    $customerid = $data['customerid'] ?? '';
    $addressid  = $data['addressid'] ?? '';
    $orderdate  = $data['orderdate'] ?? '';
    $ordertime  = $data['ordertime'] ?? '';
    $paidamount = floatval($data['paidamount'] ?? 0);
    $paymode    = $data['paymode'] ?? '';
    $paydate    = $data['paydate'] ?? '';
    $adminid    = $_SESSION['adminid'] ?? 0;

    if (
        !$customerid || !$addressid || !$orderdate ||
        !$ordertime || !$paidamount || !$paymode || !$paydate
    ) {
        echo json_encode([
            "status" => "failed",
            "message" => "Missing payment data"
        ]);
        return;
    }

    // 🔒 START TRANSACTION
    mysqli_begin_transaction($conn);

    /* ===============================
       1️⃣ INSERT PAYMENT
    =============================== */

    $insertSql = "
        INSERT INTO catering_payments
        (customer_id, address_id, paid_date, paid_amount, pay_mode, admin_id)
        VALUES
        ('$customerid', '$addressid', '$paydate', '$paidamount', '$paymode', '$adminid')
    ";

    if (!mysqli_query($conn, $insertSql)) {
        mysqli_rollback($conn);
        echo json_encode([
            "status" => "failed",
            "message" => "Payment insert failed"
        ]);
        return;
    }

    /* ===============================
       2️⃣ GET ORDER DETAILS
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
       3️⃣ CALCULATE NEW PAID AMOUNT
    =============================== */

    $newPaidAmount = $order['paid_amount'] + $paidamount;
    $paymentStatus = ($newPaidAmount >= $order['grand_total']) ? 1 : 0;

    /* ===============================
       4️⃣ GET MAX BILLING ID (KEY CHANGE)
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
       5️⃣ UPDATE ORDER
    =============================== */

    $updateSql = "
        UPDATE catering_orders
        SET
            paid_amount    = '$newPaidAmount',
            billing_id     = '$billingId',
            payment_status = '$paymentStatus'
        WHERE order_id = '{$order['order_id']}'
    ";

    if (!mysqli_query($conn, $updateSql)) {
        mysqli_rollback($conn);
        echo json_encode([
            "status" => "failed",
            "message" => "Order update failed"
        ]);
        return;
    }

    /* ===============================
       6️⃣ GENERATE RECEIPT (ONLY WHEN FULLY PAID)
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

    // ✅ COMMIT
    mysqli_commit($conn);

    echo json_encode([
        "status" => "success",
        "message" => "Payment saved successfully",
        "paid_amount" => $newPaidAmount,
        "grand_total" => $order['grand_total'],
        "payment_status" => $paymentStatus,
        "billing_id" => $billingId
    ]);
}
