<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OnlineOPD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="landing-page">
    <?php include("../includes/header.php"); ?>

    <div class="landing-bg">
        <!-- Background -->
        <img src="../assets/images/hospital_bg.jpg" alt="Hospital Background" class="bg-img">

        <!-- Slogan -->
        <div class="slogan" style="color: #B8EFFF">Your Health, Our Concern</div>

        <!-- Sliding doctor and nurse -->
        <img src="../assets/images/doctor_right.png" alt="Doctor" class="slide-image-right">
        <img src="../assets/images/nurse_left.png" alt="Nurse" class="slide-image-left">

        <!-- Buttons -->
        <div class="landing-content">
            <h1 style="text-shadow: 4px 4px 4px rgba(0, 0, 0, 0.5);">You are:</h1>
            <div class="landing-buttons">
                <a href="../patient/signin.php"><button>Patient</button></a>
                <a href="../doctor/signin.php"><button>Doctor</button></a>
                <a href="../admin/signin.php"><button>Admin</button></a>
            </div>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>
</html>
