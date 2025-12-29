<?php
header('Content-Type: application/json');

include('config.php');   // keep as your project already has; change path if this file is in a subfolder
include('dblayer.php');  // keep your dblayer if used
session_start();

// Read JSON body
$jsondata = file_get_contents('php://input');
$data = json_decode($jsondata, true);

$adminname = $data['adminname'] ?? "";
$adminmobile = $data['adminmobile'] ?? "";
$adminid = $data['adminid'] ?? "";
$adminpass = $data['adminpass'] ?? "";
$load = $data['load'] ?? "";

// Route
if ($load == "registeradmin") {
    registeradmin($conn);
} else if ($load == "adminloginbymobile") {
    adminloginbymobile($conn);
} else if ($load == "logout") {
    logout();
} else {
    echo json_encode(['code' => '400', 'status' => 'fail', 'message' => 'Invalid load']);
    exit;
}

// ---------- functions ----------

function logout(){
    setcookie("adminid", "", time() - 3600, "/");
    session_destroy();
    echo json_encode(['code'=>'200','status'=>'success']);
    exit;
}

function registeradmin($conn){
    global $adminname, $adminmobile, $adminpass;

    if (empty($adminname) || empty($adminmobile) || empty($adminpass)) {
        echo json_encode(['code'=>'400','status'=>'fail','message'=>'All fields required']);
        return;
    }

    // Keep same insertion style (no hashing) as requested
    $name = $conn->real_escape_string($adminname);
    $mobile = $conn->real_escape_string($adminmobile);
    $pass = $conn->real_escape_string($adminpass);

    $admininsertquery = "INSERT INTO `admins`(`admin_name`, `admin_mobile`, `admin_password`) 
                        VALUES ('$name','$mobile','$pass')";

    if (function_exists('setData')) {
        $resultquery = setData($conn, $admininsertquery);
        if ($resultquery == "Record created") {
            echo json_encode(['code'=>'200','status'=>'success']);
        } else {
            echo json_encode(['code'=>'400','status'=>'fail','message'=>'Insert failed']);
        }
    } else {
        if ($conn->query($admininsertquery) === TRUE) {
            echo json_encode(['code'=>'200','status'=>'success']);
        } else {
            echo json_encode(['code'=>'400','status'=>'fail','message'=>'Insert failed: '.$conn->error]);
        }
    }
}

function adminloginbymobile($conn){
    global $adminmobile, $adminpass;

    // Basic backend validation for presence
    if (empty($adminmobile) || empty($adminpass)) {
        echo json_encode(['code'=>'400','status'=>'fail','message'=>'All fields are required']);
        return;
    }

    // Validate format: either 10-digit mobile (digits only) OR valid email
    $isEmail = filter_var($adminmobile, FILTER_VALIDATE_EMAIL);
    $isMobile = preg_match('/^[0-9]{10}$/', $adminmobile);

    if (!$isEmail && !$isMobile) {
        echo json_encode(['code'=>'400','status'=>'fail','message'=>'Invalid email or mobile format']);
        return;
    }

    // Prepare statement: match admin_mobile OR admin_email and admin_password
    $stmt = $conn->prepare("SELECT adminid, admin_name, admin_mobile, admin_email, admin_password, role, status, reset_token, reset_expiry 
                            FROM `admins` 
                            WHERE (admin_mobile = ? OR admin_email = ?) AND admin_password = ?");
    if (!$stmt) {
        echo json_encode(['code'=>'500','status'=>'fail','message'=>'Prepare failed: '.$conn->error]);
        return;
    }

    $stmt->bind_param("sss", $adminmobile, $adminmobile, $adminpass);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    if (count($rows) > 0) {
        // set session and cookie as your original code did
        $_SESSION['role'] = $rows[0]['role'];
        $_SESSION['adminid'] = $rows[0]['adminid'];
        setcookie("adminid", $rows[0]['adminid'], time() + (86400 * 30), "/");

        echo json_encode(['code'=>'200','status'=>'success','data'=>$rows]);
    } else {
        echo json_encode(['code'=>'400','status'=>'fail','message'=>'Invalid credentials']);
    }

    $stmt->close();
}
?>
