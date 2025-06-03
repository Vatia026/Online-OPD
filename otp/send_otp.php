<?php
session_start();
require '../includes/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = rand(100000, 999999);
    $_SESSION['signup_otp'] = $otp;
    $email = $_POST['email'];

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Your SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'aavashbiswas1234@gmail.com'; // your email
    $mail->Password = 'anit zryj lqbn odid';     // your password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('aavashbiswas1234@gmail.com', 'OnlineOPD');
    $mail->addAddress($email);
    $mail->Subject = "Your OTP for OnlineOPD";
    $mail->Body = "Your OTP is $otp";

    if ($mail->send()) {
        echo "OTP sent successfully!";
    } else {
        echo "OTP failed to send.";
    }
}
