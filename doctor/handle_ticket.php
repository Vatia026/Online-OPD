<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes\vendor\phpmailer\phpmailer\src/Exception.php';
require '../includes\vendor\phpmailer\phpmailer\src/PHPMailer.php';
require '../includes\vendor\phpmailer\phpmailer\src/SMTP.php';

session_start();
include("../includes/db.php");

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../pages/landing.php");
    exit;
}

function sendAppointmentEmail($patient_email, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aavashbiswas1234@gmail.com';     // Replace with your Gmail
        $mail->Password   = 'anit zryj lqbn odid';       // Replace with your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('aavashbiswas1234@gmail.com', 'Online OPD');
        $mail->addAddress($patient_email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}

if (isset($_POST['action'])) {
    list($action, $id) = explode("-", $_POST['action']);

    // Get booking, patient and doctor info
    $query = $conn->query("
    SELECT b.date, b.time, p.email AS patient_email, d.fullname AS doctor_name, d.profile_picture AS doctor_image 
    FROM booking b
    JOIN patient p ON b.patient_id = p.id
    JOIN doctor d ON b.doctor_id = d.id
    WHERE b.id = $id
");

    $info = $query->fetch_assoc();

    $doctor_name = htmlspecialchars($info['doctor_name']);
   $doctor_image = "https://yourdomain.com/" . htmlspecialchars($info['doctor_image']);
    $patient_email = $info['patient_email'];
    $appointment_datetime = date("F j, Y", strtotime($info['date'])) . " at " . date("g:i A", strtotime($info['time']));

    if ($action === 'approve') {
        $conn->query("UPDATE booking SET status='approved' WHERE id=$id");
        $_SESSION['success'] = "Appointment approved successfully";

        $subject = "Appointment Approved by Dr. $doctor_name";
        $body = "
            <div style='text-align:center;font-family:sans-serif;'>
                <img src='$doctor_image' style='width:120px;height:120px;border-radius:50%;border:3px solid #2ecc71;object-fit:cover;' alt='Doctor Image'>
                <h2 style='color:#2ecc71;'>Dr. $doctor_name</h2>
                <p>Your appointment has been <strong style='color:#2ecc71;'>approved</strong>.</p>
                <p>Please be ready before <strong>$appointment_datetime</strong>.</p>
                <p style='margin-top:15px;color:#666;'>Thank you for using <strong>OnlineOPD</strong>.</p>
            </div>
        ";
        sendAppointmentEmail($patient_email, $subject, $body);

    } elseif ($action === 'disapprove') {
        $conn->query("DELETE FROM booking WHERE id=$id");
        $_SESSION['success'] = "Appointment has been removed";

        $subject = "Appointment Disapproved by Dr. $doctor_name";
        $body = "
            <div style='text-align:center;font-family:sans-serif;'>
                <img src='$doctor_image' style='width:120px;height:120px;border-radius:50%;border:3px solid #e74c3c;object-fit:cover;' alt='Doctor Image'>
                <h2 style='color:#e74c3c;'>Dr. $doctor_name</h2>
                <p>Your appointment has been <strong style='color:#e74c3c;'>disapproved</strong>.</p>
                <p>You can now book a new appointment via <strong>OnlineOPD</strong>.</p>
            </div>
        ";
        sendAppointmentEmail($patient_email, $subject, $body);
    }
}

header("Location: dashboard.php");
exit;
