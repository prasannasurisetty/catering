<?php

include('config.php');
include('dblayer.php');
session_start();

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);
$load = $data["load"] ?? "";
$orderdate = $data["orderdate"] ?? "";
$ordertime = $data["ordertime"] ?? "";
$customerid   = $data['customerid'] ?? null;
$addressid    = $data['addressid'] ?? null;




if ($load == "savemenu") {
    savemenu($conn);
} else if ($load == "cancelmenu") {
    cancelmenu($conn);
} else if ($load == "allorders") {
    allorders($conn);
} else if ($load == "fetchmenu") {
    fetchmenu($conn);
} else if ($load == "updateorder") {
    updateorder($conn);
} else if ($load == "checkorder") {
    checkorder($conn);
}


function checkorder($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    $customerid = $data['customerid'] ?? null;
    $addressid  = $data['addressid'] ?? null;
    $orderdate  = $data['orderdate'] ?? null;
    $ordertime  = $data['ordertime'] ?? null;

    $sql = "
        SELECT order_id 
        FROM catering_orders
        WHERE customer_id = '$customerid'
          AND address_id  = '$addressid'
          AND order_date  = '$orderdate'
          AND order_time  = '$ordertime'
          AND order_status != 0
          AND payment_status = 0
       AND delivered_status = 0

        LIMIT 1
    ";

    $res = getData($conn, $sql);

    echo json_encode([
        "exists" => !empty($res)
    ]);
}




function updateorder($conn)
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $data = json_decode(file_get_contents("php://input"), true);

    $customerid   = $data['customerid'] ?? null;
    $addressid    = $data['addressid'] ?? null;
    $orderdate    = $data['orderdate'] ?? null;
    $ordertime    = $data['ordertime'] ?? null;

    $plates_count = (int)($data['plates_count'] ?? 0);
    $plate_cost   = (float)($data['plate_cost'] ?? 0);
    $total_amount = (float)($data['total_amount'] ?? 0);
    $grand_total  = (float)($data['grand_total'] ?? 0);

    $fooditems = $data['fooditems'] ?? [];
    $services  = $data['services'] ?? [];

    if (!$customerid || !$addressid || !$orderdate || !$ordertime) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required data"
        ]);
        return;
    }

    mysqli_begin_transaction($conn);

    try {

        /* ================= FETCH ORDER IDS ================= */
        $order = getData(
            $conn,
            "SELECT plate_id, services_id
             FROM catering_orders
             WHERE customer_id='$customerid'
               AND address_id='$addressid'
               AND order_date='$orderdate'
               AND order_time='$ordertime'
               AND order_status != 0
               AND payment_status = 0
               AND delivered_status = 0
             LIMIT 1"
        );

        if (empty($order)) {
            throw new Exception("Order not found or cannot be updated");
        }

        $plate_id    = (int)$order[0]['plate_id'];
        $services_id = $order[0]['services_id']; // may be NULL

        /* ====================================================
           FOOD ITEMS – STRING BASED DIFF
        ==================================================== */

        $dbItems = getData(
            $conn,
            "SELECT item FROM catering_fooditems
             WHERE customer_id='$customerid'
               AND address_id='$addressid'
               AND plate_id='$plate_id'
               AND order_date='$orderdate'"
        );

        $dbSet = [];
        foreach ($dbItems as $row) {
            $dbSet[$row['item']] = true;
        }

        $uiSet = [];
        foreach ($fooditems as $item) {
            $item = mysqli_real_escape_string($conn, trim($item));
            if ($item) {
                $uiSet[$item] = true;
            }
        }

        foreach ($dbSet as $item => $_) {
            if (!isset($uiSet[$item])) {
                setData(
                    $conn,
                    "DELETE FROM catering_fooditems
                     WHERE customer_id='$customerid'
                       AND address_id='$addressid'
                       AND plate_id='$plate_id'
                       AND item='$item'
                       AND order_date='$orderdate'"
                );
            }
        }

        foreach ($uiSet as $item => $_) {
            if (!isset($dbSet[$item])) {
                setData(
                    $conn,
                    "INSERT INTO catering_fooditems
                     (customer_id,address_id,plate_id,item,order_date)
                     VALUES
                     ('$customerid','$addressid','$plate_id','$item','$orderdate')"
                );
            }
        }

        /* ====================================================
           SERVICES – DIFF + CLEAR services_id IF EMPTY
        ==================================================== */

        $hasServices = false;
        $services_amount = 0;

        if ($services_id) {

            $dbServices = getData(
                $conn,
                "SELECT services_name, services_cost
                 FROM catering_services
                 WHERE customer_id='$customerid'
                   AND address_id='$addressid'
                   AND services_id='$services_id'"
            );

            $dbSrvMap = [];
            foreach ($dbServices as $srv) {
                $dbSrvMap[$srv['services_name']] = (float)$srv['services_cost'];
            }
        } else {
            $dbSrvMap = [];
        }

        $uiSrvMap = [];
        foreach ($services as $srv) {
            $name = mysqli_real_escape_string($conn, trim($srv['name'] ?? ''));
            if ($name === '') continue;

            $cost = (float)($srv['cost'] ?? 0);
            $uiSrvMap[$name] = $cost;
        }

        foreach ($dbSrvMap as $name => $oldCost) {

            if (isset($uiSrvMap[$name])) {

                $hasServices = true;
                $newCost = $uiSrvMap[$name];

                if ($newCost != $oldCost) {
                    setData(
                        $conn,
                        "UPDATE catering_services
                         SET services_cost='$newCost'
                         WHERE customer_id='$customerid'
                           AND address_id='$addressid'
                           AND services_id='$services_id'
                           AND services_name='$name'"
                    );
                }

                $services_amount += $newCost;
                unset($uiSrvMap[$name]);
            } else {

                setData(
                    $conn,
                    "DELETE FROM catering_services
                     WHERE customer_id='$customerid'
                       AND address_id='$addressid'
                       AND services_id='$services_id'
                       AND services_name='$name'"
                );
            }
        }

        foreach ($uiSrvMap as $name => $cost) {

            $hasServices = true;

            setData(
                $conn,
                "INSERT INTO catering_services
                 (customer_id,address_id,services_id,services_name,services_cost)
                 VALUES
                 ('$customerid','$addressid','$services_id',
                  '$name','$cost')"
            );

            $services_amount += $cost;
        }

        /* ===== CLEAR services_id IF NO SERVICES LEFT ===== */
        if (!$hasServices) {

            setData(
                $conn,
                "UPDATE catering_orders
                 SET services_id = NULL
                 WHERE customer_id='$customerid'
                   AND address_id='$addressid'
                   AND order_date='$orderdate'
                   AND order_time='$ordertime'"
            );

            $services_id = null;
        }

        /* ================= UPDATE ORDER TOTALS ================= */

        setData(
            $conn,
            "UPDATE catering_orders SET
                order_count     = '$plates_count',
                plate_cost      = '$plate_cost',
                total_amount    = '$total_amount',
                services_amount = '$services_amount',
                grand_total     = '$grand_total'
             WHERE customer_id='$customerid'
               AND address_id='$addressid'
               AND order_date='$orderdate'
               AND order_time='$ordertime'"
        );

        mysqli_commit($conn);

        echo json_encode([
            "status"  => "success",
            "message" => "Order updated successfully"
        ]);
    } catch (Exception $e) {

        mysqli_rollback($conn);

        echo json_encode([
            "status"  => "error",
            "message" => $e->getMessage()
        ]);
    }
}




function fetchmenu($conn)
{
    global $customerid, $addressid, $orderdate, $ordertime;

    $sql = "
        SELECT 
            co.order_count,
            co.plate_cost,
            co.total_amount,
            cf.item,
            cs.services_name,
            cs.services_cost
        FROM catering_orders co
        LEFT JOIN catering_fooditems cf ON cf.plate_id = co.plate_id
        JOIN catering_services cs ON cs.services_id = co.services_id
        WHERE co.customer_id = '$customerid'
          AND co.address_id = '$addressid'
          AND co.order_date = '$orderdate'
          AND co.order_time = '$ordertime'
          AND co.order_status = 1
        GROUP BY cf.item,cs.services_name, cs.services_cost
    ";

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


function allorders($conn)
{
    $sql = "SELECT * FROM catering_orders WHERE order_status = 1";
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





function cancelmenu($conn)
{
    global $customerid, $addressid, $orderdate, $ordertime;


    if (!$customerid || !$addressid || !$orderdate || !$ordertime) {
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
          AND order_time = '$ordertime'
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
    $advance_amount = (float)($data['advance_amount'] ?? 0);
    $pay_mode       = $data['pay_mode'] ?? '';
    $adminid        = $_SESSION['adminid'] ?? 0;
    $billing_id = null;




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

            $item = mysqli_real_escape_string($conn, trim($item));
            if (!$item) continue;

            setData(
                $conn,
                "INSERT INTO catering_fooditems
         (customer_id, address_id, plate_id, item, order_date)
         VALUES
         ('$customerid','$addressid','$plate_id','$item','$orderdate')"
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
        $payment_status = ($advance_amount >= $grand_total) ? 1 : 0;

        setData(
            $conn,
            "INSERT INTO catering_orders
     (customer_id, address_id, plate_id, services_id,
      order_date, order_time,
      order_count, plate_cost,
      total_amount, services_amount, grand_total,
      paid_amount, payment_status,
      order_status, delivered_status,
      admin_id)
     VALUES
     ('$customerid','$addressid','$plate_id','$services_id',
      '$orderdate','$ordertime',
      '$plates_count','$plate_cost',
      '$total_amount','$services_amount','$grand_total',
      '$advance_amount','$payment_status',
      '1','0','$adminid')"
        );
        $order_id = mysqli_insert_id($conn);


        if ($advance_amount > 0) {



            setData(
                $conn,
                "INSERT INTO catering_payments
         (customer_id, address_id, paid_date,
          paid_amount, pay_mode, admin_id)
         VALUES
         ('$customerid','$addressid',CURDATE(),
          '$advance_amount','$pay_mode','$adminid')"
            );
            $billing_id = mysqli_insert_id($conn);
        }
        if ($billing_id) {
            setData(
                $conn,
                "UPDATE catering_orders
         SET billing_id = '$billing_id'
         WHERE order_id = '$order_id'"
            );
        }
        if ($payment_status == 1) {

            $checkReceiptSql = "
            SELECT receipt_id
            FROM catering_receipts
            WHERE billing_id = '$billing_id'
            LIMIT 1
        ";

            $checkRes = mysqli_query($conn, $checkReceiptSql);

            if ($checkRes && mysqli_num_rows($checkRes) == 0) {

                $insertReceiptSql = "
                INSERT INTO catering_receipts
                (billing_id, customer_id, address_id, receipt_date)
                VALUES
                ('$billing_id', '$customerid', '$addressid', CURDATE())
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
