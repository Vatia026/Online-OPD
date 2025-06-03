

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes\vendor\phpmailer\phpmailer\src/Exception.php';
require '../includes\vendor\phpmailer\phpmailer\src/PHPMailer.php';
require '../includes\vendor\phpmailer\phpmailer\src/SMTP.php';

session_start();
include("../includes/db.php");

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: signin.php");
    exit;
}

$patient = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $specialization = $_POST['specialization'] ?? '';

    if (empty($doctor_id) || empty($date) || empty($time) || empty($specialization)) {
        die("<p style='color:red;'>All fields are required.</p><p><a href='dashboard.php'>Go Back</a></p>");
    }

    if (strtotime($date) <= strtotime(date("Y-m-d"))) {
        die("<p style='color:red;'>At least 1 day gap is needed for appointments.</p><p><a href='dashboard.php'>Go Back</a></p>");
    }

    // Generate ticket number like: BK20240509-0001
    $prefix = "BK" . date("Ymd") . "-";
    $result = $conn->query("SELECT COUNT(*) AS total FROM booking WHERE date = '$date'");
    $row = $result->fetch_assoc();
    $count = $row['total'] + 1;
    $ticket = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

    // Insert into booking table
    $stmt = $conn->prepare("INSERT INTO booking 
        (doctor_id, patient_id, specialization, date, time, status) 
        VALUES (?, ?, ?, ?, ?, 'disapproved')");
    $stmt->bind_param("iisss", 
        $doctor_id, 
        $patient['id'], 
        $specialization, 
        $date, 
        $time
    );
    $stmt->execute();

    if ($stmt->affected_rows > 0): 
        // Fetch doctor info
        $doctor_query = $conn->query("SELECT fullname, email, profile_picture FROM doctor WHERE id = $doctor_id");
        $doctor = $doctor_query->fetch_assoc();

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'aavashbiswas1234@gmail.com';  // use your Gmail
            $mail->Password   = 'anit zryj lqbn odid';    // use Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('aavashbiswas1234@gmail.com', 'Online OPD System');
            $mail->addAddress($doctor['email'], $doctor['fullname']);

            $mail->Subject = 'New Appointment Booked';
            $mail->Body    = "Patient {$patient['fullname']} booked an appointment on $date at $time. See details on your dashboard.";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
        }

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Booking Confirmation</title>
            <link rel="stylesheet" href="../assets/css/style.css">
            <style>
                .confirmation-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: #f5fff8;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
                    max-width: 800px;
                    margin: 60px auto;
                }
                .confirmation-text {
                    flex: 1;
                    padding-right: 30px;
                }
                .confirmation-text h2 {
                    color: #2ecc71;
                }
                .confirmation-image {
                    text-align: center;
                }
                .confirmation-image img {
                    width: 120px;
                    height: 120px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid #2ecc71;
                }
                .confirmation-image p {
                    margin-top: 10px;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <?php include("../includes/header.php"); ?>
            <div class="confirmation-container">
                <div class="confirmation-text">
                    <h2>Booking Submitted</h2>
                    <p>Your appointment has been successfully submitted and is pending doctor approval.</p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                    <p><strong>Time:</strong> <?= htmlspecialchars($time) ?></p>
                    <p><strong>Ticket No:</strong> <?= htmlspecialchars($ticket) ?></p>
                    <p><a href="dashboard.php">← Back to Dashboard</a></p>
                </div>
                <div class="confirmation-image">
                    <img src="<?= htmlspecialchars($doctor['profile_picture']) ?>" alt="Doctor Image">
                    <p>Dr. <?= htmlspecialchars($doctor['fullname']) ?></p>
                </div>
            </div>
            <?php include("../includes/footer.php"); ?>
        </body>
        </html>
    <?php
    else:
        echo "<p style='color:red;'>Failed to save booking. Please try again.</p><a href='dashboard.php'>Back</a>";
    endif;
} else {
    header("Location: dashboard.php");
    exit;
}
?>
