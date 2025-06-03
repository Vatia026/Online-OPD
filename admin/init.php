<?php
session_start();
include("../includes/db.php");

// If admin account already exists, block access
$res = $conn->query("SELECT COUNT(*) as total FROM admin");
if ($res->fetch_assoc()['total'] > 0) {
    header("Location: signin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    header("Location: signin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Setup - OnlineOPD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
    <form method="POST">
        <h3>Admin First-Time Setup</h3>
        <input type="text" name="username" placeholder="Create Username" required>
        <input type="password" name="password" placeholder="Create Password" required>
        <button type="submit">Create Admin</button>
    </form>
</main>

<?php include("../includes/footer.php"); ?>
</body>
</html>
