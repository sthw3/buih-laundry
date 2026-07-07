<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No Service ID provided.");
}
$id = (int) $_GET['id'];

$del1 = $conn->prepare("DELETE FROM appointment_service WHERE Service_ID = ?");
$del1->bind_param("i", $id);
$del1->execute();

$del2 = $conn->prepare("DELETE FROM service WHERE Service_ID = ?");
$del2->bind_param("i", $id);


if ($del2->execute()) {
    echo "<script>alert('Service deleted successfully'); window.location='manageService.php';</script>";
} else {
    echo "<script>alert('Failed to delete service.'); window.location='manageService.php';</script>";
}
exit;
?>