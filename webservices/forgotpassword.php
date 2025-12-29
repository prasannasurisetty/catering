<?php
include('config.php');
include('dblayer.php');

// ✅ Include PHPMailer manually
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set response type
header('Content-Type: application/json');


// Read input from frontend
$data = json_decode(file_get_contents('php://input'), true);

// Extract input
$load = $data['load'] ?? '';
$email = trim($data['email'] ?? '');
$token = trim($data['token'] ?? '');
$newPassword = trim($data['password'] ?? '');

// Route requests
if ($load === 'send_reset_link') {
    send_reset_link($conn, $email);
} elseif ($load === 'reset_password') {
    reset_password($conn, $token, $newPassword);
} else {
    echo json_encode(['code' => '400', 'status' => 'error', 'message' => 'Invalid request']);
    exit;
}

function send_reset_link($conn, $email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['code' => '422', 'status' => 'error', 'message' => 'Invalid email format.']);
        return;
    }

    // Check if email exists
    $user = getData($conn, "SELECT * FROM admins WHERE admin_email = '$email'");
    if (empty($user)) {
        echo json_encode(['code' => '404', 'status' => 'error', 'message' => 'Email not found.']);
        return;
    }

    // Generate token and expiry
    $token = bin2hex(random_bytes(32));
    date_default_timezone_set("Asia/Kolkata");
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Save in DB
    $update = "UPDATE admins SET reset_token='$token', reset_expiry='$expiry' WHERE admin_email='$email'";
    $res = setData($conn, $update);
    if ($res !== "Record created") {
        echo json_encode(['code' => '500', 'status' => 'error', 'message' => 'Could not generate reset link.']);
        return;
    }

    // Build reset link
    $resetLink = "http://localhost/catering/resetpassword.php?token=$token";

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'workabc04@gmail.com'; // your Gmail
        $mail->Password = 'tyswmracqwojixxx';   // your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('workabc04@gmail.com', 'HDN Admin System');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'HDN Password Reset';
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;font-size:15px;'>
                <p>Hi,</p>
                <p>Click the link below to reset your password (valid for 10 minutes):</p>
                <p><a href='$resetLink' style='color:#F3681E;'>Reset Password</a></p>
                <p>If you did not request this, please ignore this message.</p>
                <br><p>– HDN Admin System</p>
            </div>
        ";

        $mail->send();
        echo json_encode(['code' => '200', 'status' => 'success', 'message' => 'Reset link sent successfully.']);
    } catch (Exception $e) {
        echo json_encode(['code' => '500', 'status' => 'error', 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
    }
}

function reset_password($conn, $token, $newPassword) {
    if (strlen($newPassword) < 6) {
        echo json_encode(['code' => '422', 'status' => 'error', 'message' => 'Password must be at least 6 characters.']);
        return;
    }

    $query = "SELECT admin_email, admin_password FROM admins WHERE reset_token='$token' AND reset_expiry > NOW()";
    $user = getData($conn, $query);

    if (empty($user)) {
        echo json_encode(['code' => '401', 'status' => 'error', 'message' => 'Invalid or expired token.']);
        return;
    }

    $email = $user[0]['admin_email'];
    $oldPassword = $user[0]['admin_password'];

    if ($newPassword === $oldPassword) {
        echo json_encode(['code' => '409', 'status' => 'error', 'message' => 'New password cannot be the same as old one.']);
        return;
    }

    $update = "UPDATE admins 
               SET admin_password='$newPassword', reset_token=NULL, reset_expiry=NULL 
               WHERE admin_email='$email'";
    $res = setData($conn, $update);

    if ($res === "Record created") {
        echo json_encode(['code' => '200', 'status' => 'success', 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['code' => '500', 'status' => 'error', 'message' => 'Failed to update password.']);
    }
}
?>
