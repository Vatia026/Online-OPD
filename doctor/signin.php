<?php
session_start();
include("../includes/db.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM doctor WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['role'] = 'doctor';
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $stmt2 = $conn->prepare("SELECT * FROM tempdoctor WHERE username = ?");
        $stmt2->bind_param("s", $username);
        $stmt2->execute();
        $res2 = $stmt2->get_result();

        if ($res2->num_rows === 1) {
            $error = "Your registration is under verification by admin.";
        } else {
            $error = "Doctor not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Sign In</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
<form method="POST">
    <div class="signup-top-right">
        <a href="signup.php">Sign Up</a>
    </div>
    
    <h3>Doctor Login</h3>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Sign In</button>
    <a href="../doctor/doctor_reset_password.php">Forgot Password?</a>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</form>

</main>

<?php include("../includes/footer.php"); ?>
</body>
</html>
