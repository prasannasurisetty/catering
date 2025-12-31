<?php
include('config.php');
include('dblayer.php');
session_start();

// Read JSON input
$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);

// Extract main action
$load = $data["load"] ?? "";
$adminid = $data["adminid"] ?? "";


if ($load == "save_customer") {
    registerCustomer($conn);
    exit;
}

if ($load == "type_instructions") {
    type_instructions($conn);
    exit;
}

if ($load == "fetchinfo") {
    fetchinfo($conn);
    exit;
}

if ($load == "check_phone1") {
    check_phone1($conn, $data);
    exit;
}

if ($load == "getUser") {
    getUser($conn);
}



// ==============================================================================
//  CHECK PHONE DUPLICATION
// ==============================================================================
function check_phone1($conn, $data)
{
    $phone1 = trim($data["phone1"] ?? "");

    if ($phone1 == "") {
        echo json_encode(["success" => true]);
        return;
    }

    $check = getData($conn, "SELECT CustomerID FROM customers WHERE Phone1='$phone1'");
    if (count($check) > 0) {
        echo json_encode(["success" => false, "message" => "This phone number is already registered."]);
    } else {
        echo json_encode(["success" => true]);
    }
}



// ==============================================================================
//  FETCH FOOD TYPE INSTRUCTIONS
// ==============================================================================
function type_instructions($conn)
{
    $sql = "SELECT * FROM foodtype WHERE activity = 1 ORDER BY sno ASC";
    $result = getData($conn, $sql);

    echo json_encode([
        "status" => count($result) > 0 ? "success" : "error",
        "data"   => $result
    ]);
}



// ==============================================================================
//  FETCH FULL CUSTOMER INFO
// ==============================================================================
function fetchinfo($conn)
{
    // Read JSON input also
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    // Customer ID from POST/GET/JSON
    $customerid = $_POST['customerid'] ??
        ($_GET['customerid'] ??
            ($data['customerid'] ?? null));

    if (!$customerid) {
        echo json_encode(["status" => "error", "message" => "Customer ID missing"]);
        return;
    }

    //--------------------------------------------
    // 1. CUSTOMER BASIC INFO
    //--------------------------------------------
    $cust = getData(
        $conn,
        "SELECT CustomerID, CustomerName, Phone1, Email, address_id 
         FROM customers 
         WHERE CustomerID='$customerid'"
    );

    if (empty($cust)) {
        echo json_encode(["status" => "error", "message" => "Customer not found"]);
        return;
    }

    //--------------------------------------------
    // 2. DELIVERY ADDRESSES
    //--------------------------------------------
    // VERY IMPORTANT:
    // Your JS expects: flatno | street | area | landmark | pincode | addresslink | aid
    //--------------------------------------------
    $address = getData(
        $conn,
        "SELECT 
            aid,
            flatno,
            street,
            area,
            landmark,
            pincode,
            addresslink,
            monthlysub,
            address_ph_number,
            cid
         FROM address
         WHERE cid='$customerid'
         ORDER BY aid ASC"
    );

    //--------------------------------------------
    // 3. SPECIAL REQUIREMENTS
    //--------------------------------------------
    $requirements = getData(
        $conn,
        "SELECT 
            sr.sno,
            ft.type,
            sr.food_type,
            sr.requirement
         FROM special_requirement sr
         LEFT JOIN foodtype ft
            ON ft.sno = sr.food_type
         WHERE sr.customer_id='$customerid'
         ORDER BY sr.sno ASC"
    );

    //--------------------------------------------
    // 4. SEND FINAL RESPONSE
    //--------------------------------------------
    echo json_encode([
        "status"       => "success",
        "customer"     => $cust[0],
        "address"      => $address,
        "requirements" => $requirements
    ]);
}





function registerCustomer($conn)
{
    global $storeVar;

    $storeVar = 0;
    $data = json_decode(file_get_contents("php://input"), true);

    $incomingCustomerId = $data['customer_id'] ?? null;
    $reallyNew = empty($incomingCustomerId);

    $name   = mysqli_real_escape_string($conn, $data['name'] ?? '');
    $phone1 = mysqli_real_escape_string($conn, $data['phone1'] ?? '');
    $email  = mysqli_real_escape_string($conn, $data['email'] ?? '');

    $addresses  = $data['addresses'] ?? [];
    $specialreq = $data['specialreq'] ?? [];

    // 1. CUSTOMER CREATE / UPDATE

    $existingByPhone = getData($conn, "SELECT CustomerID FROM customers WHERE Phone1='$phone1' LIMIT 1");

    if ($reallyNew) {

        if (count($existingByPhone) > 0) {
            // Customer exists — edit mode
            $customerID = $existingByPhone[0]['CustomerID'];
        } else {
            // New insertion
            $sql = "INSERT INTO customers (CustomerName, Phone1, Email)
                    VALUES ('$name','$phone1','$email')";
            mysqli_query($conn, $sql);
            $customerID = mysqli_insert_id($conn);
        }
    } else {
        // Editing customer
        $customerID = $incomingCustomerId;

        if (count($existingByPhone) > 0 && $existingByPhone[0]['CustomerID'] != $customerID) {
            echo json_encode(["success" => false, "message" => "Phone already used by another customer"]);
            return;
        }

        mysqli_query(
            $conn,
            "UPDATE customers SET
                CustomerName='$name',
                Phone1='$phone1',
                Email='$email'
             WHERE CustomerID='$customerID'"
        );
    }

    // 2. ADDRESS INSERT / UPDATE

    $firstAddressID = null;
    $i = 0;

    foreach ($addresses as $addr) {

        $addrID   = $addr['address_id'] ?? null;
        $house_no = mysqli_real_escape_string($conn, $addr['house_no'] ?? '');
        $street   = mysqli_real_escape_string($conn, $addr['street'] ?? '');
        $area     = mysqli_real_escape_string($conn, $addr['area'] ?? '');
        $landmark = mysqli_real_escape_string($conn, $addr['landmark'] ?? '');
        $pincode  = mysqli_real_escape_string($conn, $addr['pincode'] ?? '');
        $maplink  = mysqli_real_escape_string($conn, $addr['map_link'] ?? '');
        $monthlysub = mysqli_real_escape_string($conn, $addr['monthlysub'] ?? '0');
        $delivery_contact_no = mysqli_real_escape_string($conn, $addr['delivery_contact_no'] ?? '');


        // Update existing address
        if (!empty($addrID)) {

            mysqli_query(
                $conn,
                "UPDATE address SET
                    flatno='$house_no',
                    street='$street',
                    area='$area',
                    landmark='$landmark',
                    pincode='$pincode',
                    address_ph_number='$delivery_contact_no',
                    addresslink='$maplink',
                    monthlysub='$monthlysub'
                 WHERE aid='$addrID' AND cid='$customerID'"
            );

            if ($i == 0) $firstAddressID = $addrID;
            $i++;
            continue;
        }

        // For truly new customer: always insert
        if ($reallyNew) {

            mysqli_query(
                $conn,
                "INSERT INTO address (cid, flatno, street, area, landmark, pincode, address_ph_number, addresslink, monthlysub)
                 VALUES ('$customerID','$house_no','$street','$area','$landmark','$pincode','$delivery_contact_no','$maplink','$monthlysub')"
            );

            $newID = mysqli_insert_id($conn);
            if ($i == 0) $firstAddressID = $newID;
            $i++;
            continue;
        }

        // For existing customer → check duplicate
        $dup = getData(
            $conn,
            "SELECT aid FROM address
             WHERE cid='$customerID'
               AND flatno='$house_no'
               AND street='$street'
             LIMIT 1"
        );

        if (count($dup) > 0) {

            $eid = $dup[0]['aid'];
            mysqli_query(
                $conn,
                "UPDATE address SET
                    area='$area',
                    landmark='$landmark',
                    pincode='$pincode',
                    address_ph_number='$delivery_contact_no',
                    addresslink='$maplink',
                    monthlysub='$monthlysub'
                 WHERE aid='$eid'"
            );

            if ($i == 0) $firstAddressID = $eid;
        } else {

            mysqli_query(
                $conn,
                "INSERT INTO address (cid, flatno, street, area, landmark, pincode, address_ph_number, addresslink)
                 VALUES ('$customerID','$house_no','$street','$area','$landmark','$pincode','$delivery_contact_no','$maplink')"
            );

            $newID = mysqli_insert_id($conn);
            if ($i == 0) $firstAddressID = $newID;
        }

        $i++;
    }

    // 3. UPDATE customers.address_id

    if ($firstAddressID) {
        mysqli_query(
            $conn,
            "UPDATE customers SET address_id='$firstAddressID'
             WHERE CustomerID='$customerID'"
        );
    }

    // 4. SPECIAL REQUIREMENTS
    if (empty($specialreq)) {

        $checkQuery = "SELECT * FROM special_requirement WHERE customer_id = '$customerID'";
        $checkResult = getData($conn, $checkQuery);
        if ($checkResult) {
            $deleteQuery = "DELETE FROM special_requirement WHERE customer_id = '$customerID'";
            $deleteResult = setData($conn, $deleteQuery);
        }
    }

    foreach ($specialreq as $req) {

        $reqID       = $req['sno'] ?? null;
        $foodtype_id = mysqli_real_escape_string($conn, $req['foodtype_id'] ?? '');
        $requirement = mysqli_real_escape_string($conn, $req['requirement'] ?? '');

        if (!$foodtype_id || !$requirement) continue;


        $fetchSpecial = "SELECT * FROM special_requirement WHERE sno = '$reqID'";
        $result = getData($conn, $fetchSpecial);

        $fetchCount = "SELECT * FROM special_requirement WHERE customer_id = '$customerID'";
        $countResult = getData($conn, $fetchCount);
        $newSnos = array_column($specialreq, 'sno');

        if ($customerID != "" && !empty($newSnos)) {

            $deleteQuery = "DELETE FROM special_requirement WHERE customer_id = '$customerID' AND sno NOT IN (" . implode(',', $newSnos) . ")";
            $deleteResult = setData($conn, $deleteQuery);
            $storeVar = 7;
        }




        if (count($result) > 0) {
            if ($result[0]['requirement'] == $requirement && $result[0]['food_type'] == $foodtype_id) continue;
            mysqli_query(
                $conn,
                "UPDATE special_requirement SET
                    food_type='$foodtype_id',
                    requirement='$requirement'
                 WHERE sno='$reqID'"
            );
        } else {
            mysqli_query(
                $conn,
                "INSERT INTO special_requirement (customer_id, food_type, requirement)
                 VALUES ('$customerID','$foodtype_id','$requirement')"
            );
        }
    }


    // 5. RESPONSE

    echo json_encode([
        "success"          => true,
        "message"          => $reallyNew ? "Customer registered" : "Customer updated",
        "customer_id"      => $customerID,
        "first_address_id" => $firstAddressID,
        "newCustomerID"    => $customerID

    ]);
}











function getUser($conn)
{

    global $adminid;
    $selectQuery = "SELECT ad.adminid,
                            ad.admin_name,
                            ad.admin_mobile,
                            ad.admin_email,
                            ur.userType
                            FROM admins ad 
                            JOIN userrole ur ON ad.role = ur.sno
                            WHERE ad.adminid = '$adminid'";
    $selectResult = getData($conn, $selectQuery);

    echo json_encode([
        "status" => count($selectResult) > 0 ? "success" : "error",
        "data"   => $selectResult
    ]);
}
