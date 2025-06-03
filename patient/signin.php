<?php
session_start();
include("../includes/db.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM patient WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['role'] = 'patient';
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Sign In</title>
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
    
    <h3>Patient ligin</h3>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Sign In</button>
    <p><a href="reset_password.php">Forgot Password?</a></p>


    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</form>

</main>

<?php include("../includes/footer.php"); ?>
</body>
</html>
