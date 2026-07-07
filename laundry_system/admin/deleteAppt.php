<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No appointment ID provided.");
}
$id = (int) $_GET['id'];

// Delete related service records first (avoids foreign key errors)
$del1 = $conn->prepare("DELETE FROM appointment_service WHERE Appointment_ID = ?");
$del1->bind_param("i", $id);
$del1->execute();

// Delete the appointment itself
$del2 = $conn->prepare("DELETE FROM appointment WHERE Appointment_ID = ?");
$del2->bind_param("i", $id);

if ($del2->execute()) {
    echo "<script>alert('Appointment deleted successfully'); window.location='manageAppt.php';</script>";
} else {
    echo "<script>alert('Failed to delete appointment.'); window.location='manageAppt.php';</script>";
}
exit;
?>