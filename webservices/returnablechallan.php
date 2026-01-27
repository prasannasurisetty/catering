<?php
include('config.php');
include('dblayer.php');

header('Content-Type: application/json');

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);
$load = $data["load"] ?? "";

if ($load == "loadreturnablechallan") {
    loadreturnablechallan($conn);
}

function loadreturnablechallan($conn)
{
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents("php://input"), true);

    $customerid = (int)($input['customerid'] ?? 0);
    $addressid  = (int)($input['addressid'] ?? 0);
    $orderid    = (int)($input['orderid'] ?? 0);

    if (!$customerid || !$addressid || !$orderid) {
        echo json_encode([
            'code' => 400,
            'status' => 'Missing parameters'
        ]);
        return;
    }

    /* ================= ORGANIZATION ================= */
    $orgSql = "
        SELECT oi.organization_name,
               CONCAT(oa.flatno, ', ', oa.street, ', ', oa.area, ', ',
                      oa.district, ' - ', oa.pincode) AS address
        FROM organization_info oi
        JOIN organization_services os ON os.org_id = oi.id
        JOIN organization_address oa ON oa.sno = os.sno
        LIMIT 1
    ";
    $org = getData($conn, $orgSql)[0];

    /* ================= CUSTOMER ================= */
    $custSql = "
        SELECT c.CustomerName, c.Phone1,
               CONCAT(a.flatno, ', ', a.street, ', ', a.area, ', ',
                      a.pincode) AS address
        FROM customers c
        JOIN address a ON a.cid = c.CustomerID
        WHERE c.CustomerID = '$customerid'
          AND a.aid = '$addressid'
        LIMIT 1
    ";
    $customer = getData($conn, $custSql)[0];

    /* ================= UTENSILS ISSUED ================= */
    $itemsSql = "
        SELECT 
            utensils_name AS name,
            issued_qty AS qty
        FROM catering_utensils
        WHERE order_id = '$orderid'
    ";
    $items = getData($conn, $itemsSql);

    /* ================= RESPONSE ================= */
    echo json_encode([
        'code' => 200,
        'status' => 'success',
        'data' => [
            'challan_no' => 'RDC-' . date('YmdHis'),
            'date' => date('d/m/Y'),
            'time' => date('h:i A'),

            'company' => [
                'name' => $org['organization_name'],
                'address' => $org['address']
            ],

            'customer' => [
                'name' => $customer['CustomerName'],
                'phone' => $customer['Phone1'],
                'address' => $customer['address']
            ],

            'items' => $items
        ]
    ]);
}
