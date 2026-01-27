<?php

include('config.php');
include('dblayer.php');


/* ================= INPUT ================= */

$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);

$load = $data["load"] ?? "";

$id                = $data["id"] ?? "";
$organization_name = $data["organization_name"] ?? "";
$legal_type        = $data["legal_type"] ?? "";
$pan               = $data["pan"] ?? "";
$cin               = $data["cin"] ?? "";
$gstin             = $data["gstin"] ?? "";

/* ================= ROUTER ================= */

if ($load == "saveOrganization") {
    saveOrganization($conn);
} 
else if ($load == "fetchOrganization") {
    fetchOrganization($conn);
} 
else if ($load == "getOrganization") {
    getOrganization($conn);
} 
else if ($load == "toggleOrgStatus") {
    toggleOrgStatus($conn);
}

/* ================= FUNCTIONS ================= */


/* ---------- SAVE / UPDATE ---------- */
function saveOrganization($conn)
{
    global $id, $organization_name, $legal_type, $pan, $cin, $gstin;

    if (!$organization_name || !$legal_type || !$pan) {
        echo json_encode([
            "code" => 400,
            "status" => "error",
            "message" => "Required fields missing"
        ]);
        return;
    }

    if ($id == "") {

        $sql = "
            INSERT INTO organization_info
            (organization_name, legal_type, pan, cin, gstin)
            VALUES
            ('$organization_name','$legal_type','$pan','$cin','$gstin')
        ";

        $result = setData($conn, $sql);

    } else {

        $sql = "
            UPDATE organization_info SET
                organization_name = '$organization_name',
                legal_type        = '$legal_type',
                pan               = '$pan',
                cin               = '$cin',
                gstin             = '$gstin'
            WHERE id = '$id'
        ";

        $result = setData($conn, $sql);
    }

    if ($result) {
        echo json_encode([
            "code" => 200,
            "status" => "success",
            "message" => "Organization saved successfully"
        ]);
    } else {
        echo json_encode([
            "code" => 500,
            "status" => "error",
            "message" => "Save failed"
        ]);
    }
}


/* ---------- FETCH ALL ---------- */
function fetchOrganization($conn)
{
    $sql = "
        SELECT id, organization_name, legal_type, pan, cin, gstin, status
        FROM organization_info
        ORDER BY id DESC
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


/* ---------- FETCH SINGLE ---------- */
function getOrganization($conn)
{
    global $id;

    if (!$id) {
        echo json_encode([
            "code" => 400,
            "status" => "error",
            "message" => "ID missing"
        ]);
        return;
    }

    $sql = "SELECT * FROM organization_info WHERE id = '$id' LIMIT 1";

    $result = getData($conn, $sql);

    if (!empty($result)) {
        echo json_encode([
            "code" => 200,
            "status" => "success",
            "data" => $result[0]
        ]);
    } else {
        echo json_encode([
            "code" => 404,
            "status" => "Not Found",
            "data" => null
        ]);
    }
}


/* ---------- TOGGLE ACTIVE ---------- */
function toggleOrgStatus($conn)
{
    global $id;

    if (!$id) {
        echo json_encode([
            "code" => 400,
            "status" => "error",
            "message" => "ID missing"
        ]);
        return;
    }

    $sql = "
        UPDATE organization_info
        SET status = IF(status = 1, 0, 1)
        WHERE id = '$id'
    ";

    $result = setData($conn, $sql);

    if ($result) {
        echo json_encode([
            "code" => 200,
            "status" => "success",
            "message" => "Status updated"
        ]);
    } else {
        echo json_encode([
            "code" => 500,
            "status" => "error",
            "message" => "Status update failed"
        ]);
    }
}
