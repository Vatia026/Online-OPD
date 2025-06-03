<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/landing.php");
    exit;
}

$doc_id = $_POST['doc_id'];
$action = $_POST['action'];

$res = $conn->query("SELECT * FROM tempdoctor WHERE id = $doc_id");
$doc = $res->fetch_assoc();

if ($action === 'approve') {
    $stmt = $conn->prepare("INSERT INTO doctor (fullname, username, password, phone, email, specialization, experience, certificate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssis", $doc['fullname'], $doc['username'], $doc['password'], $doc['phone'], $doc['email'], $doc['specialization'], $doc['experience'], $doc['certificate']);
    $stmt->execute();
} else {
    // send disapproval email
    require '../includes/PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your@example.com';
    $mail->Password = 'yourpassword';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your@example.com', 'OnlineOPD');
    $mail->addAddress($doc['email']);
    $mail->Subject = "Doctor Application Disapproved";
    $mail->Body = <<<EOD
We are sorry we have to disapprove you.
You can apply again on our “OnlineOPD” website.
With at least a year of experience and certificate.

Thank you
Best wishes for your near future.
@from OnlineOPD team
EOD;

    $mail->send();
}

$conn->query("DELETE FROM tempdoctor WHERE id = $doc_id");
header("Location: dashboard.php");
