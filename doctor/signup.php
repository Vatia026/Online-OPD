<?php
session_start();
include("../includes/functions.php");
include("../includes/db.php");

$specializations = $conn->query("SELECT specialization FROM specialization");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cert = $_FILES['certificate']['name'];
    $temp = $_FILES['certificate']['tmp_name'];
    $filename = uniqid("cert_") . "_" . basename($cert);
    move_uploaded_file($temp, "../assets/uploads/certificates/$filename");

    $_SESSION['signup_data'] = [
        'fullname' => clean_input($_POST['fullname']),
        'username' => clean_input($_POST['username']),
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'phone' => clean_input($_POST['phone']),
        'email' => clean_input($_POST['email']),
        'specialization' => $_POST['specialization'],
        'experience' => $_POST['experience'],
        'certificate' => "assets/uploads/certificates/$filename"
    ];
    $_SESSION['signup_role'] = 'doctor';
    header("Location: ../otp/verify_otp.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Sign Up</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
    <form method="POST" enctype="multipart/form-data">
        <h3>Doctor Sign Up</h3>
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <input type="email" name="email" id="email" placeholder="Email" required>

        <select name="specialization" required>
            <option value="">Select Specialization</option>
            <?php while ($s = $specializations->fetch_assoc()): ?>
                <option value="<?= $s['specialization'] ?>"><?= $s['specialization'] ?></option>
            <?php endwhile; ?>
        </select>

        <select name="experience" required>
            <option value="">Select Experience</option>
            <?php for ($i = 1; $i <= 50; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> year<?= $i > 1 ? 's' : '' ?></option>
            <?php endfor; ?>
        </select>

        <label>Upload Qualification Certificate:</label>
        <input type="file" name="certificate" required>
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
