<?php
include('config.php');
include('dblayer.php');

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

/* ================= LOAD CHECK ================= */
if (($input['load'] ?? '') !== 'loadchallan') {
    echo json_encode(['code' => 400, 'status' => 'Invalid request']);
    exit;
}

/* ================= INPUTS ================= */
$customerid = (int)($input['customerid'] ?? 0);
$addressid  = (int)($input['addressid'] ?? 0);
$orderdate  = $input['orderdate'] ?? '';
$ordertime  = $input['ordertime'] ?? '';

if (!$customerid || !$addressid || !$orderdate || !$ordertime) {
    echo json_encode(['code' => 400, 'status' => 'Missing parameters']);
    exit;
}

/* ================= ORGANIZATION ================= */
$orgSql = "
SELECT oi.organization_name, oi.gstin, oi.cin, oi.pan,
       oa.phno, oa.email,
       CONCAT(
         oa.flatno, ', ', oa.street, ', ', oa.area, ', ',
         oa.district, ' - ', oa.pincode
       ) AS address
FROM organization_info oi
JOIN organization_services os ON os.org_id = oi.id
JOIN organization_address oa ON oa.sno = os.sno
LIMIT 1
";

$orgData = getData($conn, $orgSql);
if (!$orgData) {
    echo json_encode(['code' => 404, 'status' => 'Organization not found']);
    exit;
}
$org = $orgData[0];

/* ================= CUSTOMER ================= */
$custSql = "
SELECT c.CustomerName AS name,
       c.Phone1 AS phone,
       CONCAT(a.flatno, ', ', a.area, ', ', a.street, ', ', a.pincode) AS address
       FROM customers c
       JOIN address a ON a.cid = c.CustomerID
       WHERE c.CustomerID = $customerid
       AND a.aid = $addressid
       LIMIT 1
    ";

$custData = getData($conn, $custSql);
if (!$custData) {
    echo json_encode(['code' => 404, 'status' => 'Customer not found']);
    exit;
}
$customer = $custData[0];

/* ================= ORDERS ================= */
$orderSql = "
SELECT order_id, services_id
FROM catering_orders
WHERE customer_id = '$customerid'
  AND address_id = '$addressid'
  AND order_date = '$orderdate'
  AND order_time = '$ordertime'
  AND payment_status = 1
";

$orders = getData($conn, $orderSql);
if (!$orders) {
    echo json_encode(['code' => 404, 'status' => 'No orders found']);
    exit;
}

/* ================= COLLECT IDS ================= */
$orderIds   = [];
$serviceIds = [];

foreach ($orders as $row) {
    if (!empty($row['order_id'])) {
        $orderIds[] = (int)$row['order_id'];
    }
    if (!empty($row['services_id'])) {
        $serviceIds[] = (int)$row['services_id'];
    }
}

$orderIdList   = implode(',', array_unique($orderIds));
$serviceIdList = implode(',', array_unique($serviceIds));

/* ================= RETURNABLE (UTENSILS) ================= */
$returnableItems = [];

if ($orderIdList !== '') {
    $utensilSql = "
    SELECT utensils_name, issued_qty
    FROM catering_utensils
    WHERE order_id IN ($orderIdList)
    ";

    $utensils = getData($conn, $utensilSql) ?: [];

    foreach ($utensils as $u) {
        $returnableItems[] = [
            'name' => $u['utensils_name'],
            'qty'  => (int)$u['issued_qty']
        ];
    }
}

/* ================= UNRETURNABLE (SERVICES) ================= */
$unreturnableServices = [];

if ($serviceIdList !== '') {
    $serviceSql = "
    SELECT services_name, services_cost
    FROM catering_services
    WHERE services_id IN ($serviceIdList)
    ";

    $services = getData($conn, $serviceSql) ?: [];

    foreach ($services as $s) {
        $unreturnableServices[] = [
            'name'   => $s['services_name'],
            'amount' => (float)$s['services_cost']
        ];
    }
}

/* ================= RESPONSE ================= */
echo json_encode([
    'code' => 200,
    'status' => 'success',
    'organization' => [
        'name'    => $org['organization_name'],
        'gstin'   => $org['gstin'],
        'cin'     => $org['cin'],
        'pan'     => $org['pan'],
        'phone'   => $org['phno'],
        'email'   => $org['email'],
        'address' => $org['address']
    ],
    'customer' => $customer,
    'returnable' => [
        'challan_no' => 'RDC-' . time(),
        'items' => $returnableItems
    ],
    'unreturnable' => [
        'challan_no' => 'UDC-' . time(),
        'services' => $unreturnableServices
    ]
]);
