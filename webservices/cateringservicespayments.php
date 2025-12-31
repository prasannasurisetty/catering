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
$recoveryamt = $data['recoveryamt'] ?? "";
$deliveredtime = $data['deliveredtime'] ?? "";

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
} else if ($load == "loadpaymode") {
    loadpaymode($conn);
} else if ($load == "deliveredstatus") {
    deliveredstatus($conn);
}



function deliveredstatus($conn)
{
    header('Content-Type: application/json');

    // READ JSON INPUT
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid JSON input"
        ]);
        return;
    }

    $customerid    = $data['customerid'] ?? '';
    $addressid     = $data['addressid'] ?? '';
    $orderdate     = $data['orderdate'] ?? '';
    $ordertime     = $data['ordertime'] ?? '';
    $deliveredtime = $data['deliveredtime'] ?? '';
    $delivered     = (int)($data['delivered'] ?? 0);

    if (
        !$customerid || !$addressid ||
        !$orderdate || !$ordertime || !$deliveredtime
    ) {
        echo json_encode([
            "status" => "failed",
            "message" => "Missing required data"
        ]);
        return;
    }



    // OUT FOR DELIVERY ONLY
    $sql = "
            UPDATE catering_orders
            SET
                outfor_delivery = '$deliveredtime',
                delivered_status = '$delivered'
            WHERE customer_id = '$customerid'
              AND address_id  = '$addressid'
              AND order_date  = '$orderdate'
              AND order_time  = '$ordertime'
              AND order_status = 1
              AND delivered_status = 0
            LIMIT 1
        ";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "code" =>  "200",
            "status" => "success",
            "message" => "updated successfull"
        ]);
    } else {
        echo json_encode([
            "code" =>  "400",
            "status" => "failed",
            "message" => "updated failed"
        ]);
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
        SELECT 
            grand_total,
            paid_amount,
            recovery_amount,
            payment_status,
            (grand_total + IFNULL(recovery_amount,0) - paid_amount) AS amounttobe_paid
        FROM catering_orders
        WHERE customer_id = '$customerid'
          AND address_id  = '$addressid'
          AND order_date  = '$orderdate'
          AND order_time  = '$ordertime'
        LIMIT 1
    ";

    $result = getData($conn, $sql);

    if ($result && count($result) > 0) {
        echo json_encode([
            "status" => "success",
            "grand_total"     => $result[0]['grand_total'],
            "paid_amount"     => $result[0]['paid_amount'],
            "recovery_amount" => $result[0]['recovery_amount'],
            "amounttobe_paid" => max(0, $result[0]['amounttobe_paid']),
            "payment_status"  => $result[0]['payment_status']
        ]);
    } else {
        echo json_encode([
            "status" => "failed",
            "message" => "Order not found"
        ]);
    }
}



function paymenthistory($conn)
{
    global $orderdate, $customerid, $addressid, $ordertime;
    $sql = "SELECT c.paid_date,c.paid_amount,p.type AS pay_mode FROM `catering_payments` c 
            JOIN paymode p ON p.sno = c.pay_mode
            JOIN catering_orders co ON co.billing_id = c.billing_id
           WHERE c.customer_id = '$customerid' AND c.address_id = '$addressid' AND co.order_date = '$orderdate' AND co.order_time='$ordertime'";
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
