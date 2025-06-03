<?php
session_start();
require '../includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes\vendor\phpmailer\phpmailer\src/Exception.php';
require '../includes\vendor\phpmailer\phpmailer\src/PHPMailer.php';
require '../includes\vendor\phpmailer\phpmailer\src/SMTP.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $_SESSION['reset_email'] = $email;
    $otp = rand(100000, 999999);
    $_SESSION['reset_otp'] = $otp;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aavashbiswas1234@gmail.com';
        $mail->Password = 'anit zryj lqbn odid';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('aavashbiswas1234@gmail.com', 'OnlineOPD');
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset OTP - OnlineOPD';
        $mail->Body    = "Your OTP to reset your password is: $otp";

        $mail->send();
        header("Location: verify_reset.php");
        exit;
    } catch (Exception $e) {
        $error = "Failed to send OTP: " . $mail->ErrorInfo;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/form-style.css">
</head>
<body>
    <form method="POST">
        <h2>Forgot Your Password?</h2>
        <p>Enter your email address to receive a one-time OTP.</p>
        <input type="email" name="email" placeholder="Your email" required>
        <button type="submit">Send OTP</button>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>
</body>
</html>
