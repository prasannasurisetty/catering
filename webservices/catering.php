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


// $foodtypeid = $data["foodtypeid"] ?? "";
$cid = $data["cid"] ?? "";
$aid = $data["aid"] ?? "";
$fromdate = $data["fromdate"] ?? "";
$houseno = $data["houseno"] ?? "";
$link = $data["link"] ?? "";
$street = $data["street"] ?? "";
$area = $data["area"] ?? "";
$phone = $data["phone"] ?? "";
$landmark = $data["landmark"] ?? "";
$pincode = $data["pincode"] ?? "";
$address_id = $data["address_id"] ?? "";
$default = !empty($data['default']) ? 1 : 0;




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
} else if ($load == "addAddress") {
    addAddress($conn);
} else if ($load == "editAddress") {
    editAddress($conn);
} else if ($load == "deleteAddress") {
    deleteAddress($conn);
}




function addAddress($conn)
{
    global $cid, $houseno, $street, $area, $landmark, $phone, $link, $pincode, $default;

    $insertQuery = "INSERT INTO `address`(`cid`,`flatno`,`area`,`street`,`landmark`,`address_ph_number`,`addresslink`,`pincode`)
                    VALUES ('$cid','$houseno','$area','$street','$landmark','$phone','$link','$pincode')";
    $result = setData($conn, $insertQuery);

    if ($result) {
        $aid = $conn->insert_id;

        if ($default) {
            setDefaultAddress($conn, $cid, $aid);
        }

        echo json_encode([
            'code' => 200,
            'status' => 'success',
            'message' => 'Address added successfully!'
        ]);
    } else {
        echo json_encode([
            'code' => 400,
            'status' => 'error',
            'message' => 'Address add failed!'
        ]);
    }
}

function editAddress($conn)
{
    global $aid, $cid, $houseno, $street, $area, $landmark, $phone, $link, $pincode, $default;

    $updateQuery = "UPDATE address SET 
                        flatno='$houseno',
                        area='$area',
                        street='$street',
                        landmark='$landmark',
                        address_ph_number='$phone',
                        pincode='$pincode',
                        addresslink='$link'
                    WHERE aid='$aid'";
    $result = setData($conn, $updateQuery);

    if ($result) {
        if ($default) {
            setDefaultAddress($conn, $cid, $aid);
        }

        echo json_encode([
            'code' => 200,
            'status' => 'success',
            'message' => 'Address updated successfully!'
        ]);
    } else {
        echo json_encode([
            'code' => 400,
            'status' => 'error',
            'message' => 'Address update failed!'
        ]);
    }
}


function deleteAddress($conn)
{
    global $aid;

    if (!$aid) {
        echo json_encode([
            'code' => 400,
            'status' => 'error',
            'message' => 'Address ID missing'
        ]);
        return;
    }

    $deleteQuery = "DELETE FROM address WHERE aid = '$aid'";

    $result = setData($conn, $deleteQuery);

    if ($result == "Record created") {
        echo json_encode([
            'code' => 200,
            'status' => 'success',
            'message' => 'Address deleted!'
        ]);
    } else {
        echo json_encode([
            'code' => 400,
            'status' => 'error',
            'message' => 'Address delete failed!'
        ]);
    }
}

function setDefaultAddress($conn, $cid, $aid)
{
    $query = "UPDATE customers SET address_id = '$aid' WHERE CustomerID = '$cid'";
    return setData($conn, $query);
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

    $customerid = $data['customerid'] ?? null;
    $addressid  = $data['addressid'] ?? null;
    $orderdate  = $data['orderdate'] ?? null;
    $ordertime  = $data['ordertime'] ?? null;

    $plates_count = (int)($data['plates_count'] ?? 0);
    $plate_cost   = (float)($data['plate_cost'] ?? 0);
    $total_amount = (float)($data['total_amount'] ?? 0);
    $grand_total  = (float)($data['grand_total'] ?? 0);

    $fooditems = $data['fooditems'] ?? [];
    $services  = $data['services'] ?? [];



    // $statusCheck = getData(
    //     $conn,
    //     "SELECT order_status
    //      FROM catering_orders
    //      WHERE customer_id='$customerid'
    //        AND address_id='$addressid'
    //        AND order_date='$orderdate'
    //        AND order_time='$ordertime'
    //      LIMIT 1"
    // );

    // if (empty($statusCheck)) {
    //     echo json_encode([
    //         "status" => "error",
    //         "message" => "Order not found"
    //     ]);
    //     return;
    // }

    // if ((int)$statusCheck[0]['order_status'] === 0) {
    //     echo json_encode([
    //         "status" => "error",
    //         "message" => "Cancelled order cannot be updated"
    //     ]);
    //     return;
    // }

    if (!$customerid || !$addressid || !$orderdate || !$ordertime) {
        echo json_encode(["status" => "error", "message" => "Missing required data"]);
        return;
    }

    mysqli_begin_transaction($conn);

    try {

        /* =====================================================
           FETCH ORDER (SINGLE ROW – NO NEW INSERT)
        ===================================================== */
        $order = getData(
            $conn,
            "SELECT order_id, plate_id, services_id
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

        if (!$order) {
            throw new Exception("Order not found or cannot be updated");
        }

        $order_id   = $order[0]['order_id'];
        $plate_id   = $order[0]['plate_id'];
        $services_id = $order[0]['services_id'];

        /* =====================================================
           FOOD ITEMS (PLATE_ID BASED DIFF – NO DUPLICATES)
        ===================================================== */

        $dbItems = getData(
            $conn,
            "SELECT item FROM catering_fooditems WHERE plate_id='$plate_id'"
        );
        $dbSet = [];
        foreach ($dbItems as $row) {
            $key = strtolower(trim($row['item']));   // 🔥 NORMALIZE
            $dbSet[$key] = $row['item'];              // keep original value
        }
        $uiSet = [];
        foreach ($fooditems as $item) {
            $clean = strtolower(trim($item));         // 🔥 NORMALIZE
            if ($clean !== '') {
                $uiSet[$clean] = mysqli_real_escape_string($conn, trim($item));
            }
        }

        // ➕ INSERT NEW ITEMS
        foreach ($uiSet as $key => $originalItem) {
            if (!isset($dbSet[$key])) {
                setData(
                    $conn,
                    "INSERT INTO catering_fooditems
             (customer_id,address_id,plate_id,item,order_date)
             VALUES
             ('$customerid','$addressid','$plate_id','$originalItem','$orderdate')"
                );
            }
        }

        // ❌ DELETE REMOVED ITEMS
        foreach ($dbSet as $key => $originalItem) {
            if (!isset($uiSet[$key])) {
                setData(
                    $conn,
                    "DELETE FROM catering_fooditems
             WHERE plate_id='$plate_id'
               AND item='$originalItem'"
                );
            }
        }

        /* =====================================================
           SERVICES (SERVICES_ID BASED DIFF – SAFE UPDATE)
        ===================================================== */

        $services_amount = 0;

        $dbServices = [];
        if ($services_id) {
            $rows = getData(
                $conn,
                "SELECT sno, services_name, services_cost
                 FROM catering_services
                 WHERE services_id='$services_id'"
            );
            foreach ($rows as $srv) {
                $dbServices[$srv['sno']] = $srv;
            }
        }

        $uiIds = [];

        foreach ($services as $srv) {
            $name = mysqli_real_escape_string($conn, trim($srv['name'] ?? ''));
            if ($name === '') continue;

            $cost = (float)($srv['cost'] ?? 0);
            $services_amount += $cost;

            // 🔁 UPDATE EXISTING
            if (!empty($srv['sno']) && isset($dbServices[$srv['sno']])) {

                $db = $dbServices[$srv['sno']];
                if ($db['services_name'] !== $name || (float)$db['services_cost'] !== $cost) {
                    setData(
                        $conn,
                        "UPDATE catering_services
                         SET services_name='$name',
                             services_cost='$cost'
                         WHERE sno='{$srv['sno']}'"
                    );
                }
                $uiIds[] = $srv['sno'];
            }
            // ➕ INSERT NEW
            else {
                setData(
                    $conn,
                    "INSERT INTO catering_services
                     (customer_id,address_id,services_id,services_name,services_cost)
                     VALUES
                     ('$customerid','$addressid','$services_id','$name','$cost')"
                );
            }
        }

        // ❌ DELETE REMOVED SERVICES
        foreach ($dbServices as $id => $_) {
            if (!in_array($id, $uiIds)) {
                setData($conn, "DELETE FROM catering_services WHERE sno='$id'");
            }
        }

        /* =====================================================
           UPDATE ORDER TOTALS (SAME ROW)
        ===================================================== */

        setData(
            $conn,
            "UPDATE catering_orders SET
                order_count     = '$plates_count',
                plate_cost      = '$plate_cost',
                total_amount    = '$total_amount',
                services_amount = '$services_amount',
                grand_total     = '$grand_total'
             WHERE order_id='$order_id'"
        );

        mysqli_commit($conn);

        echo json_encode([
            "status" => "success",
            "message" => "Order updated successfully"
        ]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}



function fetchmenu($conn)
{
    global $customerid, $addressid, $orderdate, $ordertime;

    $sql = "
        SELECT 
        co.order_remarks,
            co.order_count,
            co.plate_cost,
            co.total_amount,
            cf.item,
            cs.sno,
            cs.services_name,
            cs.services_cost,
            co.order_status
        FROM catering_orders co
        LEFT JOIN catering_fooditems cf ON cf.plate_id = co.plate_id
        LEFT JOIN catering_services cs ON cs.services_id = co.services_id
        WHERE co.customer_id = '$customerid'
          AND co.address_id = '$addressid'
          AND co.order_date = '$orderdate'
          AND co.order_time = '$ordertime'
        GROUP BY cf.item,cs.sno,cs.services_name, cs.services_cost
    ";

    $result = getData($conn, $sql);

    if (!empty($result)) {
        echo json_encode([
            "code" => 200,
            "status" => "success",
            "data" => $result,
            "order_status" => $result[0]['order_status']
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
    $sql = "SELECT 
        co.order_id,
    co.order_date,
    co.order_time,
    co.customer_id,
    c.CustomerName,
    a.address_ph_number,
    a.aid AS address_id ,
    co.order_status,
    CONCAT(
        a.flatno, ', ',
        a.area, ', ',
        a.street, ', ',
        a.landmark, ', ',
        a.pincode
    ) AS full_address
    FROM catering_orders co
    JOIN customers c ON c.CustomerID = co.customer_id
    JOIN address a ON a.aid = co.address_id
    WHERE co.delivered_status != 1 AND co.order_status = 1
    
   -- AND co.order_status = 1  
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
          AND order_status = 1
          AND delivered_status = 0
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
    $remarks = $data['remarks'];

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
      order_status, delivered_status,order_remarks,
      admin_id)
     VALUES
     ('$customerid','$addressid','$plate_id','$services_id',
      '$orderdate','$ordertime',
      '$plates_count','$plate_cost',
      '$total_amount','$services_amount','$grand_total',
      '$advance_amount','$payment_status',
      '1','0','$remarks',
      '$adminid')"
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
