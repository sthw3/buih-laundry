<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No Receipt ID provided.");
}
$id = (int) $_GET['id'];

$del = $conn->prepare("DELETE FROM receipt WHERE Receipt_ID = ?");
$del->bind_param("i", $id);



if ($del->execute()) {
    echo "<script>alert('Receipt deleted successfully'); window.location='manageReceipt.php';</script>";
} else {
    echo "<script>alert('Failed to delete Receipt.'); window.location='manageReceipt.php';</script>";
}
exit;
?>