<?php
session_start();
include("../includes/session.php");
require_login('admin');

include("../includes/db.php");

// Fetch metrics
$total_bookings = $conn->query("SELECT COUNT(*) as c FROM booking")->fetch_assoc()['c'];
$total_doctors = $conn->query("SELECT COUNT(*) as c FROM doctor")->fetch_assoc()['c'];
$total_patients = $conn->query("SELECT COUNT(*) as c FROM patient")->fetch_assoc()['c'];

// Pending doctors
$pending_docs = $conn->query("SELECT * FROM tempdoctor");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - OnlineOPD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .stats-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            flex: 1;
            min-width: 200px;
            text-align: center;
        }

        .dashboard-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            margin: 20px;
        }

        .card h3 {
            margin-top: 0;
        }

        .pending-doctor {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .pending-doctor form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">

    <!-- Dashboard Metrics -->
    <div class="dashboard-grid">
        <div class="stats-box">
            <h3>Total Bookings</h3>
            <p><?= $total_bookings ?></p>
        </div>
        <div class="stats-box">
            <h3>Total Doctors</h3>
            <p><?= $total_doctors ?></p>
        </div>
        <div class="stats-box">
            <h3>Total Patients</h3>
            <p><?= $total_patients ?></p>
        </div>
    </div>

    <!-- Doctor Approval -->
<div class="card">
    <h3>Pending Doctor Approvals</h3>
    <?php if ($pending_docs->num_rows > 0): ?>
        <?php while ($doc = $pending_docs->fetch_assoc()): ?>
            <div class="pending-doctor">
                <strong><?= htmlspecialchars($doc['fullname']) ?> (<?= htmlspecialchars($doc['username']) ?>)</strong><br>
                Specialization: <?= htmlspecialchars($doc['specialization']) ?><br>
                Experience: <?= htmlspecialchars($doc['experience']) ?> year(s)<br>
                Certificate:
                <?php
                    $cert_path = $doc['certificate'];
                    if (file_exists("../$cert_path")):
                ?>
                    <a href="../<?= $cert_path ?>" target="_blank">View</a>
                <?php else: ?>
                    <span style="color:red;">File not found</span>
                <?php endif; ?>

                <form method="POST" action="approve_doctor.php">
                    <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                    <button name="action" value="approve">Approve</button>
                    <button name="action" value="disapprove">Disapprove</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No pending doctors at the moment.</p>
    <?php endif; ?>
</div>

    <!-- Reset User Password -->
    <div class="card">
        <h3>Reset User Credentials</h3>
        <form method="POST" action="reset_user.php">
            <input type="text" name="username" placeholder="Username to reset" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>

</main>

<?php include("../includes/footer.php"); ?>
</body>
</html>
