<?php

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function generate_otp() {
    return rand(100000, 999999);
}

function send_email($to, $subject, $message) {
    require_once("../includes/PHPMailer/PHPMailerAutoload.php");

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your@example.com';
    $mail->Password = 'yourpassword';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your@example.com', 'OnlineOPD');
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $message;

    return $mail->send();
}
