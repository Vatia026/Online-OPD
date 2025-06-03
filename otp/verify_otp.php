<?php
session_start();
include("../includes/db.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    $actual_otp = $_SESSION['signup_otp'] ?? null;

    if ($entered_otp == $actual_otp) {
        $data = $_SESSION['signup_data'];
        $role = $_SESSION['signup_role'];

        if ($role === 'patient') {
            $stmt = $conn->prepare("INSERT INTO patient (fullname, username, dob, password, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $data['fullname'], $data['username'], $data['dob'], $data['password'], $data['phone'], $data['email']);
            $stmt->execute();
        } elseif ($role === 'doctor') {
            $stmt = $conn->prepare("INSERT INTO tempdoctor (fullname, username, password, phone, email, specialization, experience, certificate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssis", $data['fullname'], $data['username'], $data['password'], $data['phone'], $data['email'], $data['specialization'], $data['experience'], $data['certificate']);
            $stmt->execute();
        }

        unset($_SESSION['signup_data'], $_SESSION['signup_otp'], $_SESSION['signup_role']);
        header("Location: ../{$role}/signin.php");
        exit;
    } else {
        $error = "Incorrect OTP. Try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
    <form method="POST">
        <h3>Verify OTP</h3>
        <input type="text" name="otp" placeholder="Enter OTP sent to email" required>
        <button type="submit">Verify</button>
        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    </form>
</main>

<?php include("../includes/footer.php"); ?>
</body>
</html>
