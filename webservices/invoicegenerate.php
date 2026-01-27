<?php
include('config.php');
include('dblayer.php');

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

if (($input['load'] ?? '') !== 'loadinvoice') {
    echo json_encode(['code' => 400, 'status' => 'Invalid request']);
    exit;
}

$customerid = (int)($input['customerid'] ?? 0);
$addressids = $input['addressid'] ?? '';
$orderdate   = $input['orderdate'] ?? '';
$ordertime     = $input['ordertime'] ?? '';

if (!$customerid || !$addressids || !$orderdate || !$ordertime) {
    echo json_encode(['code' => 400, 'status' => 'Missing parameters']);
    exit;
}

// $addressids = array_map('intval', $addressids);
// $addressIdList = implode(',', $addressids);
$primaryAddressId = $addressids;

/* ================= ORGANIZATION ================= */
$orgSql = "
SELECT oi.organization_name, oi.gstin,oi.cin, oi.pan, oa.phno, oa.email,
       CONCAT(oa.flatno,', ',oa.street,', ',oa.area,', ',oa.district,' - ',oa.pincode) address
FROM organization_info oi
JOIN organization_services os ON os.org_id = oi.id
JOIN organization_address oa ON oa.sno = os.sno
";

$orgData = getData($conn, $orgSql);
if (!$orgData) {
    echo json_encode(['code' => 404, 'status' => 'Organization not found']);
    exit;
}
$org = $orgData[0];

/* ================= CUSTOMER ================= */
$custSql = "
SELECT c.CustomerName, c.Phone1, a.address_ph_number,
       CONCAT(a.flatno,', ',a.area,', ',a.street,', ',a.pincode) address
FROM customers c
JOIN address a ON a.cid = c.CustomerID
WHERE c.CustomerID = $customerid
  AND a.aid = $primaryAddressId
LIMIT 1
";

$customerData = getData($conn, $custSql);
if (!$customerData) {
    echo json_encode(['code' => 404, 'status' => 'Customer not found']);
    exit;
}
$customer = $customerData[0];

/* ================= ORDERS ================= */
$orderSql = "SELECT * FROM catering_orders
WHERE customer_id = '$customerid' AND address_id = '$primaryAddressId'  AND order_date = '$orderdate' AND order_time = '$ordertime' AND payment_status = 1
;
";

$orders = getData($conn, $orderSql);
if (!$orders) {
    echo json_encode(['code' => 404]);
    exit;
}

/* ================= TAX ================= */
$tax = [
    'cgst' => 2.5,
    'sgst' => 2.5,
    'igst' => 0
];

/* ================= RESPONSE ================= */
echo json_encode([
    'code' => 200,
    'status' => 'success',
    'data' => [
        'organization' => [
            'name'    => $org['organization_name'],
            'gstin'   => $org['gstin'],
            'cin'     => $org['cin'],
            'pan'     => $org['pan'],
            'phone'   => $org['phno'],
            'email'   => $org['email'],
            'address' => $org['address']
        ],
        'invoice' => [
            'invoice_no'   => 'INV-' . date('YmdHis'),
            'invoice_date' => date('Y-m-d')
        ],
        'customer' => $customer,
        'orders'   => $orders,
        'tax'      => $tax
    ]
]);
