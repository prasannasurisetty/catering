<?php
include('config.php');
include('dblayer.php');
session_start();

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);

$cid = $data["cid"] ?? "";
$load = $data["load"] ?? "";

$flatno   = $data["flatno"] ?? "";
$street   = $data["street"] ?? "";
$area     = $data["area"] ?? "";
$landmark = $data["landmark"] ?? "";
$del_mobile   = $data["del_mobile"] ?? "";
$link     = $data["link"] ?? "";
$load     = $data["load"] ?? "";



if($load == "get_address"){
    getCustomerAddress($conn);
}
else if($load == "insert_address"){
    insertAddress($conn);
}

function insertAddress($conn){
    global $cid,$flatno, $area, $street, $landmark, $del_mobile, $link;
    try{
     $sql = "INSERT INTO `address`(`cid`,`flatno`,`area`, `street`, `landmark`, `address_ph_number`, `addresslink`) 
                VALUES (?, ?, ?,?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bind_param("issssss", $cid,$flatno,$area, $street, $landmark, $del_mobile, $link);

        if ($stmt->execute()) {
              echo json_encode( [
                "status" => true,
                "message" => "Address inserted successfully"
            ]);
        } else {
              echo json_encode( [
                "status" => false,
                "message" => "Database insert failed",
                "error"   => $stmt->error
            ]);
        }

    } catch (Exception $e) {
          echo json_encode([
            "status" => false,
            "message" => "Exception occurred",
             "error_type" => get_class($e),
            "error_message" => $e->getMessage(),
            "trace" => $e->getTraceAsString()
        ]);
    }
}


function getCustomerAddress($conn){
    global $cid;

    try {
        $selectAid = "SELECT address_id FROM customers WHERE CustomerID = '$cid'";
        $aidResult = getData($conn, $selectAid);
        $defaultID = !empty($aidResult[0]['address_id']) ? $aidResult[0]['address_id'] : null;

        $selectAddresses = "
            SELECT 
                aid,
                cid,
                flatno,
                pincode,
                area,
                street,
                landmark,
                addresslink,
                monthlysub,
                address_ph_number,
                CASE WHEN aid = '$defaultID' THEN 1 ELSE 0 END AS isDefault
            FROM address
            WHERE cid = '$cid'
            ORDER BY aid
        ";

        $addresses = getData($conn, $selectAddresses);

        echo json_encode([
            "success" => true,
            "defaultAddressId" => $defaultID,
            "count" => count($addresses),
            "data" => $addresses,
            "message" => "Address details fetched successfully."
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error_type" => get_class($e),
            "error_message" => $e->getMessage(),
            "trace" => $e->getTraceAsString()
        ]);
    }
}



?>