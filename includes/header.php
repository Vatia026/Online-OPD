<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<div class="header" style="display: flex; justify-content: space-between; align-items: center; position: relative; padding: 10px 20px;">
    <!-- Logo on the left -->
    <a href="<?= isset($_SESSION['user']) ? '../' . $_SESSION['role'] . '/dashboard.php' : 'landing.php' ?>">
        <img src="../assets/images/logo.png" alt="Logo" height="40">
    </a>

    <!-- Centered heading -->
    <h2 style="position: absolute; left: 50%; transform: translateX(-50%); margin: 0;">OnlineOPD</h2>

    <!-- Right section - only shown when user is logged in -->
    <?php if (isset($_SESSION['user'])): ?>
    <div style="display: flex; align-items: center; gap: 10px;">
        <!-- User profile picture -->
        <a href="../pages/user.php">
            <?php 
            $profile_img = isset($_SESSION['user']['profile_picture']) && file_exists("../assets/uploads/profiles/{$_SESSION['user']['profile_picture']}") 
                ? "../assets/uploads/profiles/{$_SESSION['user']['profile_picture']}" 
                : "../assets/images/default_profile.png";
            ?>
            <img src="<?= $profile_img ?>" 
                 alt="User Profile" 
                 height="40" 
                 style="border-radius: 50%; cursor: pointer; object-fit: cover;">
        </a>
        <!-- Logout button -->
        <a href="../pages/logout.php">
            <button>Logout</button>
        </a>
    </div>
    <?php endif; ?>
</div>