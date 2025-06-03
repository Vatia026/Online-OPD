<?php
session_start();
include("../includes/functions.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['signup_data'] = [
        'fullname' => clean_input($_POST['fullname']),
        'username' => clean_input($_POST['username']),
        'dob' => $_POST['dob'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'phone' => clean_input($_POST['phone']),
        'email' => clean_input($_POST['email']),
    ];
    $_SESSION['signup_role'] = 'patient';
    header("Location: ../otp/verify_otp.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Sign Up</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
    <form method="POST">
        <h3>Patient Sign Up</h3>
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="date" name="dob" max="<?= date('Y-m-d') ?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="email" name="email" id="email" placeholder="Email" required>
        <div class="button-container">
        <button type="button" onclick="sendOTP()">Send OTP</button>
        <button type="submit">Next</button>
        </div>
    </form>
</main>

<?php include("../includes/footer.php"); ?>

<script>
function sendOTP() {
    const email = document.getElementById('email').value;
    fetch('../otp/send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}`
    }).then(res => res.text()).then(alert);
}
</script>
</body>
</html>
