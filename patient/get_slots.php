<?php
include("../includes/db.php");

$doctor = $_GET['doctor'];
$date = $_GET['date'];

$slots = [
    "10:00-10:30", "10:30-11:00", "11:00-11:30", "11:30-12:00",
    "12:00-12:30", "12:30-1:00", "1:00-1:30", "1:30-2:00"
];

$reserved = [];
$res = $conn->query("SELECT time FROM booking WHERE doctor_id=$doctor AND date='$date'");
while ($row = $res->fetch_assoc()) {
    $reserved[] = $row['time'];
}

foreach ($slots as $slot) {
    $disabled = in_array($slot, $reserved) ? "disabled" : "";
    echo "<label><input type='radio' name='time' value='$slot' $disabled> $slot</label><br>";
}
