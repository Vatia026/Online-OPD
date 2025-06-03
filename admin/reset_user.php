<?php
session_start();
include("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Try in all 3 user tables
    foreach (['admin', 'patient', 'doctor'] as $table) {
        $conn->query("UPDATE $table SET password='$new_password' WHERE username='$username'");
    }
}
header("Location: dashboard.php");
