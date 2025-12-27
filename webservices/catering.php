<?php

include('config.php');
include('dblayer.php');
session_start();

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);
$load = $data["load"] ?? "";

if ($load == "savemenu") {
    savemenu($conn);
} else if ($load == "cancelmenu") {
    cancelmenu($conn);
} 




function cancelmenu($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    $customerid = $data['customerid'] ?? null;
    $addressid  = $data['addressid'] ?? null;
    $orderdate  = $data['orderdate'] ?? null;

    if (!$customerid || !$addressid || !$orderdate) {
        echo json_encode([
            "status" => "failed",
            "message" => "Missing required data"
        ]);
        return;
    }

    $sql = "
        UPDATE catering_orders
        SET order_status = 0
        WHERE customer_id = '$customerid'
          AND address_id  = '$addressid'
          AND order_date  = '$orderdate'
    ";

    $result = setData($conn, $sql);

    if ($result) {
        echo json_encode([
            "status" => "success",
            "message" => "Order cancelled"
        ]);
    } else {
        echo json_encode([
            "status" => "failed",
            "message" => "Unable to cancel order"
        ]);
    }
}



function savemenu($conn)
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $data = json_decode(file_get_contents("php://input"), true);

    $customerid   = $data['customer_id'] ?? null;
    $addressid    = $data['address_id'] ?? null;
    $orderdate    = $data['order_date'] ?? null;
    $ordertime    = $data['order_time'] ?? null;

    $plates_count = (int)($data['plates_count'] ?? 0);
    $plate_cost   = (float)($data['plate_cost'] ?? 0);
    $total_amount = (float)($data['total_amount'] ?? 0);
    $grand_total  = (float)($data['grand_total'] ?? 0);

    $fooditems    = $data['fooditems'] ?? [];
    $services     = $data['services'] ?? [];

    /* ================= plate_id from fooditems ================= */
    $res = getData($conn, "SELECT MAX(plate_id) AS max_id FROM catering_fooditems");
    $plate_id = (!empty($res) && $res[0]['max_id']) ? ((int)$res[0]['max_id'] + 1) : 1;

    /* ================= services_id ONLY IF services exist ================= */
    $services_id = 0;
    if (!empty($services)) {
        $res = getData($conn, "SELECT MAX(services_id) AS max_id FROM catering_services");
        $services_id = (!empty($res) && $res[0]['max_id'])
            ? ((int)$res[0]['max_id'] + 1)
            : 1;
    }

    mysqli_begin_transaction($conn);

    try {

        /* ================= INSERT FOOD ITEMS ================= */
        foreach ($fooditems as $item) {

            $itemname = mysqli_real_escape_string($conn, $item['name'] ?? '');
            $itemqty  = (int)($item['qty'] ?? 1);

            if (!$itemname) continue;

            setData(
                $conn,
                "INSERT INTO catering_fooditems
                 (customer_id, address_id, plate_id,
                  item_name, item_qty, order_date)
                 VALUES
                 ('$customerid','$addressid','$plate_id',
                  '$itemname','$itemqty','$orderdate')"
            );
        }

        /* ================= INSERT SERVICES (OPTIONAL) ================= */
        $services_amount = 0;

        foreach ($services as $srv) {

            $srvname = mysqli_real_escape_string($conn, $srv['name'] ?? '');
            $srvcost = (float)($srv['cost'] ?? 0);

            if (!$srvname || !$srvcost) continue;

            $services_amount += $srvcost;

            setData(
                $conn,
                "INSERT INTO catering_services
                 (customer_id, address_id, services_id,
                  services_name, services_cost)
                 VALUES
                 ('$customerid','$addressid','$services_id',
                  '$srvname','$srvcost')"
            );
        }

        /* ================= INSERT ORDER ================= */
        setData(
            $conn,
            "INSERT INTO catering_orders
             (customer_id, address_id, plate_id, services_id,
              order_date, order_time,
              order_count, plate_cost,
              total_amount, services_amount, grand_total,
              order_status, payment_status, delivered_status,
              admin_id)
             VALUES
             ('$customerid','$addressid','$plate_id','$services_id',
              '$orderdate','$ordertime',
              '$plates_count','$plate_cost',
              '$total_amount','$services_amount','$grand_total',
              '1','1','0','{$_SESSION['adminid']}')"
        );

        mysqli_commit($conn);

        echo json_encode([
            "status"  => "success",
            "message" => "Order saved successfully"
        ]);
    } catch (Exception $e) {

        mysqli_rollback($conn);

        echo json_encode([
            "status"  => "error",
            "message" => $e->getMessage()
        ]);
    }
}
