<?php
include("../includes/db.php");
$specialization = $_GET['specialization'] ?? '';
$res = $conn->query("SELECT id, fullname FROM doctor WHERE specialization = '$specialization'");
while ($doc = $res->fetch_assoc()) {
    echo "<option value='{$doc['id']}'>{$doc['fullname']}</option>";
}
