<?php
session_start();
require '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($otp != $_SESSION['reset_otp']) {
        $error = "Incorrect OTP.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $email = $_SESSION['reset_email'];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE patient SET password='$hashed' WHERE email='$email'");

        unset($_SESSION['reset_email'], $_SESSION['reset_otp']);
        header("Location: signin.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP & Reset Password</title>
    <link rel="stylesheet" href="../assets/css/form-style.css">
</head>
<body>
    <form method="POST">
        <h2>Verify OTP</h2>
        <p>Enter the OTP sent to your email and choose a new password.</p>
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Reset Password</button>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>
</body>
</html>
