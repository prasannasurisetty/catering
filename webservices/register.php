<?php
include('config.php');
include('dblayer.php');

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);

$load = $data['load'] ?? "";
$customerid = $data['customerid'] ?? "";
$customername = $data['customername'] ?? "";
$primaryphone = $data['primaryphone'] ?? "";
$billingphone = $data['billingphone'] ?? "";
$deliveryphone = $data['deliveryphone'] ?? "";
$flatno = $data['flatno'] ?? "";
$street = $data['street'] ?? "";
$area = $data['area'] ?? "";
$landmark = $data['landmark'] ?? "";
$billingaddress = $data['billingaddress'] ?? "";
$deliveryaddress = $data['deliveryaddress'] ?? "";
$email = $data['email'] ?? "";
$periodicity = $data['periodicity'] ?? "";
$map = $data['map'] ?? "";
$menuDate = $data['menuDate'] ?? "";

$deliveryflatno = $data['deliveryflatno'] ?? "";
$deliverystreet = $data['deliverystreet'] ?? "";
$deliveryarea = $data['deliveryarea'] ?? "";
$deliverylandmark = $data['deliverylandmark'] ?? "";

$billingflatno = $data['billingflatno'] ?? "";
$billingstreet = $data['billingstreet'] ?? "";
$billingarea = $data['billingarea'] ?? "";
$billinglandmark = $data['billinglandmark'] ?? "";

$searchvalue = $data['searchvalue'] ?? "";

if ($load == "register") {
    register($conn);
} else if ($load == "fetchbymobile") {
    fetch_by_mobile($conn);
} else if ($load == "fetchbyid") {
    fetch_by_id($conn);
} else if ($load == "fetchbycustomername") {
    fetch_by_name($conn);
} else if ($load == "add_delivery_address") {
    add_deliveryaddress($conn);
} else if ($load == "add_billing_address") {
    add_billingaddress($conn);
} else if ($load == "loadMenubyDate") {
    loadMenuByDate($conn);
} else if ($load == "search") {
    searchcustomer($conn);
}




function searchcustomer($conn)
{
    global $searchvalue;
    $searchvalue = trim($searchvalue);

    if (empty($searchvalue)) {
        $jsonresponse = array('code' => '400', 'status' => "Search value is empty", "data" => "");
        echo json_encode($jsonresponse);
        return;
    }

    $selectquery = "SELECT CustomerName, CustomerID, Phone1 FROM customers
       WHERE REPLACE(LOWER(CustomerID), ' ', '') LIKE ?
        OR REPLACE(LOWER(CustomerName), ' ', '') LIKE ?
        OR REPLACE(Phone1, ' ', '') LIKE ?
        LIMIT 10";

    $stmt = $conn->prepare($selectquery);
    $searchTerm = "%" . strtolower(str_replace(' ', '', trim($searchvalue))) . "%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $jsonresponse = array('code' => '200', 'status' => "Success", 'data' => $data);
    } else {
        $jsonresponse = array('code' => '200', 'status' => "No Data");
    }
    echo json_encode($jsonresponse);

    // $result = getData($conn,$selectquery);

    // if(count($result) > 0){
    //     $jsonresponse = array('code' => '200', 'status' => "Success",'data'=>$result);
    //     echo json_encode($jsonresponse);
    // }
    // else{
    //     $jsonresponse = array('code' => '200', 'status' => "No Data");
    //     echo json_encode($jsonresponse);
    // }
}

// SELECT s.Date, s.foodid, f.ItemName, s.foodtype,s.subcategory as subsno,ft.type,sb.subcategory
function loadMenuByDate($conn)
{
    global $menuDate;

    // Main menu query
    $selectquery = "SELECT 
    s.Date, 
    s.foodid, 
    f.ItemName, 
    sb.foodtype AS category, 
    s.subcategory AS subsno, 
    ft.type, 
    sb.subcategory 
    FROM scheduling AS s 
    JOIN fooddetails AS f ON s.foodid = f.OptionID
    JOIN subcategory AS sb ON s.subcategory = sb.SNO
    JOIN foodtype AS ft ON sb.foodtype = ft.sno   -- < changed here
    WHERE s.Date = '$menuDate'
    ORDER BY ft.type;
    ";
    $resultquery = getData($conn, $selectquery);

    // Always get available food types
    $typeQuery = "SELECT type FROM foodtype WHERE activity = 1 ORDER BY sno";
    $foodTypes = getData($conn, $typeQuery);

    // Response structure
    if (count($resultquery) > 0) {
        $jsonresponse = array(
            'code' => '200',
            'status' => 'success',
            'data' => $resultquery,
            'foodtypes' => $foodTypes
        );
    } else {
        $jsonresponse = array(
            'code' => '200',
            'status' => 'success',
            'data' => array(),
            'foodtypes' => $foodTypes
        );
    }

    echo json_encode($jsonresponse);
}


//function for load load_periodicity
// function load_periodicity($conn){
//    $selectquery = "SELECT * FROM `periodicity`";
//    $result = getData($conn,$selectquery);
//    if(count($result) > 0){
//     $jsonresponse = array('code' => '200', 'status' => "Success",'data'=>$result);
//     echo json_encode($jsonresponse);
//    }
//    else{
//     $jsonresponse = array('code' => '200', 'status' => "Success",'data'=>"No data");
//     echo json_encode($jsonresponse);
//    }
// }


//add billing address
function add_billingaddress($conn)
{
    global $customerid, $billingaddress, $billingphone, $billingflatno, $billinglandmark, $billingstreet, $billingarea;

    $insertsql = "UPDATE `customers` 
              SET  
                  `Billing_Flatno` = '$billingflatno',
                  `Billing_Street` = '$billingstreet',
                  `Billing_Area` = '$billingarea',
                  `Billing_Landmark` = '$billinglandmark',
                  `Billing_Phonenumber` = '$billingphone'
              WHERE `CustomerID` = '$customerid'";

    $sqlresult = setData($conn, $insertsql);

    if ($sqlresult == "Record created") {
        $jsonresponse =  array('code' => '200', 'status' => 'success');
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '400', 'status' => 'fail');
        echo json_encode($jsonresponse);
    }
}

//add delivery address
function add_deliveryaddress($conn)
{
    global $customerid, $deliveryaddress, $map, $deliveryphone, $deliveryflatno, $deliverystreet, $deliveryarea, $deliverylandmark;

    $insertsql = "UPDATE `customers` 
              SET 
                  `Map` = '$map',
                  `delivery_Flatno` = '$deliveryflatno',
                  `delivery_Street` = '$deliverystreet',
                  `delivery_Area` = '$deliveryarea',
                  `delivery_Landmark` = '$deliverylandmark',
                  `delivery_Phonenumber` = '$deliveryphone'
              WHERE `CustomerID` = '$customerid'";

    $sqlresult = setData($conn, $insertsql);

    if ($sqlresult == "Record created") {
        $jsonresponse = array('code' => '200', 'status' => "Success");
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '200', 'status' => "failed");
        echo json_encode($jsonresponse);
    }
}


//register the customer
function register($conn)
{
    global $customername, $primaryphone, $email, $deliveryaddress, $deliveryphone, $map,
        $deliveryflatno, $deliverystreet, $deliveryarea, $deliverylandmark;

    $checksql = "SELECT * FROM `customers` WHERE `Phone1` = '$primaryphone'";
    $resultsql = getData($conn, $checksql);



    if (count($resultsql) > 0) {
        echo json_encode(['code' => '200', 'status' => "Exist"]);
        exit;
    }
    // $flatno,$street,$area,$landmark,$billingaddress,$deliveryaddress,$email,$periodicity,$map;

    // $insertsql = "INSERT INTO `customers`(`CustomerName`, `Phone1`, `Phone2`, `Phone3`, `FlatNo`, `Street`, `Area`, `Landmark`, `BillingAddress`, `DeliveryAddress`, `email`, `periodicity`, `Map`) 
    // VALUES ('customername','primaryphone','billingphone','deliveryphone','flatno','street','area','landmark','billingaddress','deliveryaddress','email','periodicity','map')";

    $insertsql = "INSERT INTO `customers`(`CustomerName`, `Phone1`,`Email`,Map,Delivery_Flatno,Delivery_Street,Delivery_Area,Delivery_landmark,Delivery_Phonenumber) 
    VALUES ('$customername','$primaryphone','$email','$map','$deliveryflatno','$deliverystreet','$deliveryarea','$deliverylandmark','$deliveryphone')";

    $sqlresult = setData($conn, $insertsql);

    if ($sqlresult == "Record created") {
        $jsonresponse = array('code' => '200', 'status' => "Success");
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '200', 'status' => "failed");
        echo json_encode($jsonresponse);
    }
}
//fetch customers by mobile
function  fetch_by_mobile($conn)
{
    global $primaryphone;

    $selectquery = "SELECT * FROM `customers` WHERE `Phone1` = '$primaryphone'";
    $sqlresult = getData($conn, $selectquery);

    if (count($sqlresult) > 0) {
        $jsonresponse = array('code' => '200', 'status' => "Success", 'data' => $sqlresult);
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '200', 'status' => "No Data",);
        echo json_encode($jsonresponse);
    }
}

//fetch customers by id
function  fetch_by_id($conn)
{
    global $customerid;

    $selectquery = "SELECT * FROM `customers` WHERE `CustomerId` = '$customerid'";
    $sqlresult = getData($conn, $selectquery);

    if (count($sqlresult) > 0) {
        $jsonresponse = array('code' => '200', 'status' => "Success", 'data' => $sqlresult);
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '200', 'status' => "No Data",);
        echo json_encode($jsonresponse);
    }
}

//fetch by name
function fetch_by_name($conn)
{
    global $customername;

    $selectquery = "SELECT * FROM `customers`
    WHERE LOWER(REPLACE(CustomerName, ' ', '')) = LOWER(REPLACE('$customername', ' ', ''));";
    $sqlresult = getData($conn, $selectquery);

    if (count($sqlresult) > 0) {
        $jsonresponse = array('code' => '200', 'status' => "Success", 'data' => $sqlresult);
        echo json_encode($jsonresponse);
    } else {
        $jsonresponse = array('code' => '200', 'status' => "No Data",);
        echo json_encode($jsonresponse);
    }
}
