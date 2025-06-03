<?php
session_start();
include("../includes/session.php");
require_login('patient');
include("../includes/db.php");

$patient = $_SESSION['user'];
$patient_id = $patient['id'];

// Get all specializations
$specializations = $conn->query("SELECT DISTINCT specialization FROM specialization");

// Get all doctors for JS filter
$doctors = $conn->query("SELECT id, fullname, specialization FROM doctor");
$doctorList = [];
while ($d = $doctors->fetch_assoc()) {
    $doctorList[] = $d;
}

// Get patient's appointments
$appointments = $conn->query("
    SELECT b.*, d.fullname as doctor_name 
    FROM booking b
    JOIN doctor d ON b.doctor_id = d.id
    WHERE b.patient_id = $patient_id
    ORDER BY b.date DESC, b.time DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard - OnlineOPD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .dashboard-wrapper {
            max-width: 1000px;
            margin: 40px auto;
            padding: 25px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .profile-info div {
            padding: 12px 16px;
            background: #f4fef8;
            border-left: 4px solid #2ecc71;
            border-radius: 6px;
            font-size: 15px;
        }

        .slot-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .slot-btn {
            padding: 10px 15px;
            border: 1px solid #2ecc71;
            background-color: white;
            color: #2ecc71;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            font-size: 14px;
        }

        .slot-btn:hover {
            background-color: #2ecc71;
            color: white;
        }

        .slot-btn.active {
            background-color: #27ae60;
            color: white;
        }

        .form-submit {
            margin-top: 20px;
            text-align: center;
        }

        #doctor-wrap {
            display: none;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert.success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .alert.error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        .appointments {
            margin-top: 40px;
        }

        .appointment-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid;
        }

        .status-pending { 
            color: #f39c12;
            border-left-color: #f39c12;
        }
        .status-approved { 
            color: #2ecc71;
            border-left-color: #2ecc71;
        }
        .status-disapproved { 
            color: #e74c3c;
            border-left-color: #e74c3c;
        }

        .prescription-list {
            margin-top: 10px;
        }

        .prescription-list a {
            color: #3498db;
            text-decoration: none;
        }

        .prescription-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include("../includes/header.php"); ?>

<main class="content">
    <div class="dashboard-wrapper">
        <!-- Display alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?>
                <p><small>Note: All new bookings require doctor approval.</small></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="dashboard-header">
            <h2>Welcome, <?= htmlspecialchars($patient['fullname']) ?></h2>
            <p>Your profile and booking options</p>
        </div>

        <div class="profile-info">
            <div><strong>Username:</strong> <?= htmlspecialchars($patient['username']) ?></div>
            <div><strong>DOB:</strong> <?= htmlspecialchars($patient['dob']) ?></div>
            <div><strong>Phone:</strong> <?= htmlspecialchars($patient['phone']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></div>
        </div>

        <div class="booking-form">
    <h3>Book an Appointment</h3>
    <form method="POST" action="book_ticket.php" enctype="multipart/form-data">
        <label>Select Date:</label>
        <input type="date" name="date" required min="<?= date('Y-m-d', strtotime('+2 day')); ?>">

        <label>Select Specialization:</label>
        <select name="specialization" id="specialization" required onchange="filterDoctors()">
            <option value="">-- Choose Specialization --</option>
            <?php while ($s = $specializations->fetch_assoc()): ?>
                <option value="<?= trim($s['specialization']) ?>"><?= htmlspecialchars($s['specialization']) ?></option>
            <?php endwhile; ?>
        </select>

        <div id="doctor-wrap">
            <label>Select a Doctor:</label>
            <select name="doctor_id" id="doctor" required>
                <option value="">-- Choose Doctor --</option>
            </select>
        </div>

        <label>Select a Time Slot:</label>
        <div class="slot-buttons">
            <?php
            for ($hour = 10; $hour < 17; $hour++) {
                $start = sprintf("%02d:00", $hour);
                $end = sprintf("%02d:30", $hour);
                echo "<button type='button' class='slot-btn' onclick='selectSlot(this)'>{$start}-{$end}</button>";
            }
            ?>
        </div>

        <input type="hidden" name="time" id="selected-time" required>

        <label>Upload Prescriptions (Max 5):</label>
        <input type="file" name="prescriptions[]" multiple accept="image/*,pdf">

        <div class="form-submit">
            <button type="submit">Submit Booking Request</button>
        </div>
    </form>
</div>


        <div class="appointments">
            <h3>Your Appointment Requests</h3>
            <?php if ($appointments->num_rows > 0): ?>
                <?php while ($app = $appointments->fetch_assoc()): ?>
                    <div class="appointment-card status-<?= htmlspecialchars($app['status']) ?>">
                        <h4>Dr. <?= htmlspecialchars($app['doctor_name']) ?> (<?= htmlspecialchars($app['specialization']) ?>)</h4>
                        <p><strong>Date:</strong> <?= htmlspecialchars($app['date']) ?></p>
                        <p><strong>Time:</strong> <?= htmlspecialchars($app['time']) ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-<?= htmlspecialchars($app['status']) ?>">
                                <?= ucfirst(htmlspecialchars($app['status'])) ?>
                            </span>
                        </p>
                        <?php if (!empty($app['prescription_1'])): ?>
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
                <p>No appointment requests found.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include("../includes/footer.php"); ?>

<script>
const doctorData = <?= json_encode($doctorList) ?>;

function selectSlot(button) {
    document.querySelectorAll('.slot-btn').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
    document.getElementById('selected-time').value = button.innerText;
}

function filterDoctors() {
    const selectedSpec = document.getElementById('specialization').value.trim().toLowerCase();
    const doctorSelect = document.getElementById('doctor');
    const doctorWrap = document.getElementById('doctor-wrap');

    doctorSelect.innerHTML = '<option value="">-- Choose Doctor --</option>';

    if (!selectedSpec) {
        doctorWrap.style.display = "none";
        return;
    }

    const filtered = doctorData.filter(doc =>
        doc.specialization.trim().toLowerCase() === selectedSpec
    );

    if (filtered.length > 0) {
        filtered.forEach(doc => {
            const option = document.createElement('option');
            option.value = doc.id;
            option.textContent = `Dr. ${doc.fullname} (${doc.specialization})`;
            doctorSelect.appendChild(option);
        });
        doctorWrap.style.display = "block";
    } else {
        doctorWrap.style.display = "none";
    }
}
</script>
</body>
</html>