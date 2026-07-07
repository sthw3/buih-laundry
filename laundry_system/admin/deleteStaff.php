<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No Staff ID provided.");
}
$id = (int) $_GET['id'];

$check = $conn->prepare("SELECT COUNT(*) AS total FROM appointment WHERE Staff_ID = ?");
$check->bind_param("i", $id);
$check->execute();
$count = $check->get_result()->fetch_assoc()['total'];

if($count > 0){
    echo"<script>alert('Cannot delete staff, they have existing appointments.'); window.loacaiton='manageStaff.php';</script>";
    exit;
}

$del = $conn->prepare("DELETE FROM staff WHERE Staff_ID = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    echo "<script>alert('Staff deleted successfully'); window.location='manageStaff.php';</script>";
} else {
    echo "<script>alert('Failed to delete staff.'); window.location='manageStaff.php';</script>";
}
exit;
?>