<?php
session_start();
include("../includes/db.php");

// Redirect to init if no admin account exists
$res = $conn->query("SELECT COUNT(*) as total FROM admin");
if ($res->fetch_assoc()['total'] === 0) {
    header("Location: init.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['user'] = $admin;
            $_SESSION['role'] = 'admin';
            header("Location: dashboard.php"); // ✅ Redirect works here
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Admin not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Sign In - OnlineOPD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
    <form method="POST">
        <h3>Admin Login</h3>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <?php if (!empty($error)): ?>
            <p style="color:red; text-align:center;"><?= $error ?></p>
        <?php endif; ?>
    </form>
</main>

<?php include("../includes/footer.php"); ?>
</body>
</html>
