<?php
session_start();
include("../includes/session.php");
require_login('doctor');

include("../includes/db.php");

$doctor = $_SESSION['user'];
$doctor_id = $doctor['id'];

// Fetch pending and disapproved appointments
$pending_appointments = $conn->query("
    SELECT b.*, p.fullname as patient_name, p.phone as patient_phone 
    FROM booking b
    JOIN patient p ON b.patient_id = p.id
    WHERE b.doctor_id = $doctor_id AND (b.status = 'pending' OR b.status = 'disapproved')
    ORDER BY b.date, b.time
");

// Fetch approved appointments
$approved_appointments = $conn->query("
    SELECT b.*, p.fullname as patient_name, p.phone as patient_phone 
    FROM booking b
    JOIN patient p ON b.patient_id = p.id
    WHERE b.doctor_id = $doctor_id AND b.status = 'approved'
    ORDER BY b.date, b.time
");

// Count appointments for stats
$stats = $conn->query("
    SELECT 
        SUM(status = 'pending' OR status = 'disapproved') as pending_count,
        SUM(status = 'approved') as approved_count,
        COUNT(*) as total_count
    FROM booking 
    WHERE doctor_id = $doctor_id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard - OnlineOPD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .dashboard-wrapper {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .dashboard-header h2 {
            margin-bottom: 10px;
            color: #008f5a;
        }
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .profile-info div {
            padding: 10px 15px;
            background:rgb(166, 255, 0);
            border-radius: 6px;
            border-left: 4px solid #2ecc71;
            font-size: 15px;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-card .count {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2ecc71;
            margin: 10px 0;
        }
        .appointments-section {
            margin-top: 30px;
        }
        .appointment-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-bottom: 3px solid transparent;
        }
        .tab-button.active {
            border-bottom: 3px solid #2ecc71;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .appointment-card {
            background:rgb(255, 255, 255);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
        }
        .appointment-actions {
            margin-top: 10px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
        }
        .btn-approve {
            background-color: #2ecc71;
            color: white;
            gap: 10px;
        }
        .btn-disapprove {
            background-color: #e74c3c;
            color: white;
            gap: 10px;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 3px 8px;
            border-radius: 12px;
            background: #eee;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background:rgb(81, 250, 121); color: #155724; }
        .status-disapproved { background: #f8d7da; color: #721c24; }
        .prescription-list {
            margin-top: 10px;
        }
        .prescription-list a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
    <div class="dashboard-wrapper">
        <div class="dashboard-header">
            <h2>Welcome, Dr. <?= htmlspecialchars($doctor['fullname']) ?></h2>
            <p class="subtitle">Here's your profile and dashboard options</p>
        </div>

        <div class="profile-info">
            <div><strong>Username:</strong> <?= htmlspecialchars($doctor['username']) ?></div>
            <div><strong>Phone:</strong> <?= htmlspecialchars($doctor['phone']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($doctor['email']) ?></div>
            <div><strong>Specialization:</strong> <?= htmlspecialchars($doctor['specialization']) ?></div>
            <div><strong>Experience:</strong> <?= htmlspecialchars($doctor['experience']) ?> years</div>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Appointments</h3>
                <div class="count"><?= $stats['total_count'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Approval</h3>
                <div class="count"><?= $stats['pending_count'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Approved</h3>
                <div class="count"><?= $stats['approved_count'] ?></div>
            </div>
        </div>

        <div class="appointments-section">
            <h3>Appointment Management</h3>
            
            <div class="appointment-tabs">
                <button class="tab-button active" style= color:#FF5733; onclick="openTab(event, 'pending-tab')">Pending Approval</button>
                <button class="tab-button" style= color:#3498db; onclick="openTab(event, 'approved-tab')">Approved Appointments</button>
            </div>

            <div id="pending-tab" class="tab-content active">
                <?php if ($pending_appointments->num_rows > 0): ?>
                    <?php while ($app = $pending_appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <h4><?= htmlspecialchars($app['patient_name']) ?> 
                                <span class="status-badge status-<?= htmlspecialchars($app['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($app['status'])) ?>
                                </span>
                            </h4>
                            <p><strong>Date:</strong> <?= htmlspecialchars($app['date']) ?></p>
                            <p><strong>Time:</strong> <?= htmlspecialchars($app['time']) ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($app['patient_phone']) ?></p>
                            
                            <?php if ($app['prescription_1'] || $app['prescription_2'] || $app['prescription_3'] || $app['prescription_4'] || $app['prescription_5']): ?>
                                <div class="prescription-list">
                                    <strong>Prescriptions:</strong>
                                    <ul>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if (!empty($app["prescription_$i"])): ?>
                                                <li>
                                                    <a href="<?= htmlspecialchars($app["prescription_$i"]) ?>" target="_blank">
                                                        Prescription <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="appointment-actions">
                                <form method="POST" action="handle_ticket.php" style="display: flex;">
                                    <input type="hidden" name="action" value="approve-<?= $app['id'] ?>">
                                    <button type="submit" class="btn btn-approve">Approve</button>
                                </form>
                                <form method="POST" action="handle_ticket.php" style="display: flex;">
                                    <input type="hidden" name="action" value="disapprove-<?= $app['id'] ?>">
                                    <button type="submit" class="btn btn-disapprove">Disapprove</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No pending appointments found.</p>
                <?php endif; ?>
            </div>

            <div id="approved-tab" class="tab-content">
                <?php if ($approved_appointments->num_rows > 0): ?>
                    <?php while ($app = $approved_appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <h4><?= htmlspecialchars($app['patient_name']) ?> 
                                <span class="status-badge status-approved">Approved</span>
                            </h4>
                            <p><strong>Date:</strong> <?= htmlspecialchars($app['date']) ?></p>
                            <p><strong>Time:</strong> <?= htmlspecialchars($app['time']) ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($app['patient_phone']) ?></p>
                            
                            <?php if ($app['prescription_1'] || $app['prescription_2'] || $app['prescription_3'] || $app['prescription_4'] || $app['prescription_5']): ?>
                                <div class="prescription-list">
                                    <strong>Prescriptions:</strong>
                                    <ul>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if (!empty($app["prescription_$i"])): ?>
                                                <li>
                                                    <a href="<?= htmlspecialchars($app["prescription_$i"]) ?>" target="_blank">
                                                        Prescription <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No approved appointments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include("../includes/footer.php"); ?>

<script>
function openTab(evt, tabName) {
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove("active");
    }

    const tabButtons = document.getElementsByClassName("tab-button");
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }

    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}
</script>
</body>
</html>