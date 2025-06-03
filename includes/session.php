<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to landing page if not logged in
function require_login($role = null) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['role'])) {
        header("Location: ../pages/landing.php");
        exit;
    }

    if ($role && $_SESSION['role'] !== $role) {
        header("Location: ../pages/landing.php");
        exit;
    }
}

// Example usage:
// require_login();             // any user
// require_login('admin');      // only admin
// require_login('doctor');     // only doctor
