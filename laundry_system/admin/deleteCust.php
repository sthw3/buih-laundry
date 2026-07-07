<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No Customer ID provided.");
}
$id = (int) $_GET['id'];

$check = $conn->prepare("SELECT COUNT(*) AS total FROM appointment WHERE Customer_ID = ?");
$check->bind_param("i", $id);
$check->execute();
$count = $check->get_result()->fetch_assoc()['total'];

if ($count > 0) {
    echo "<script>alert('Cannot delete customer — they have existing appointments.'); window.location='manageCustomer.php';</script>";
    exit;
}

$del = $conn->prepare("DELETE FROM customer WHERE Customer_ID = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    echo "<script>alert('Customer deleted successfully'); window.location='manageCustomer.php';</script>";
} else {
    echo "<script>alert('Failed to delete customer.'); window.location='manageCustomer.php';</script>";
}
exit;
?>