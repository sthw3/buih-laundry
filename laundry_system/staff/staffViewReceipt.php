<?php
include("../config/database.php");

// Get and validate the receipt ID from the URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid receipt ID.");
}

$query = "SELECT 
    receipt.receipt_ID AS Receipt_ID,
    receipt.Appointment_ID,
    receipt.Payment_ID,
    receipt.Total_amount,
    receipt.Issued_Date,

    customer.Customer_Name,
    customer.Cust_PhoneNum,

    payment.Payment_Method,

    GROUP_CONCAT(service.Service_Name SEPARATOR ', ') AS Services

FROM receipt

INNER JOIN appointment 
    ON receipt.Appointment_ID = appointment.Appointment_ID

INNER JOIN customer 
    ON appointment.Customer_ID = customer.Customer_ID

LEFT JOIN appointment_service 
    ON appointment.Appointment_ID = appointment_service.Appointment_ID

LEFT JOIN service 
    ON appointment_service.Service_ID = service.Service_ID

LEFT JOIN payment
    ON receipt.Payment_ID = payment.Payment_ID

WHERE receipt.receipt_ID = ?

GROUP BY receipt.receipt_ID";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query failed: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Receipt not found.");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>View Receipt #<?= htmlspecialchars($row['Receipt_ID']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body {
    background: #f0f2f7;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 40px 20px;
}

.receipt-card {
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.receipt-header {
    background: #253154;
    color: #fff;
    padding: 25px 30px;
}

.receipt-header h4 {
    margin: 0;
    font-weight: 700;
}

.receipt-header small {
    color: rgba(255,255,255,.6);
}

.receipt-body {
    padding: 30px;
}

.receipt-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.receipt-row:last-child {
    border-bottom: none;
}

.receipt-label {
    color: #6c757d;
    font-size: .9rem;
}

.receipt-value {
    font-weight: 600;
    color: #253154;
    text-align: right;
}

.total-row {
    margin-top: 10px;
    padding-top: 20px;
    border-top: 2px solid #253154;
}

.total-row .receipt-label {
    font-size: 1.1rem;
    color: #253154;
    font-weight: 700;
}

.total-row .receipt-value {
    font-size: 1.4rem;
    color: #2dd4bf;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #253154;
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    color: #2dd4bf;
}

    .print-btn {
    background: #253154;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.print-btn:hover {
    background: #1a2340;
}

@media print {
    .back-link,
    .print-btn {
        display: none !important;
    }
    body {
        background: #fff;
        padding: 0;
    }
    .receipt-card {
        box-shadow: none;
        border: none;
    }
}
</style>
</head>
<body>

<div class="receipt-card">
    <a href="staffListReceipt.php" class="back-link" style="margin: 20px 0 0 30px; margin-bottom: 20px;">
        <i class="bi bi-arrow-left"></i> Back to Receipts
    </a>

    <div class="receipt-header">
        <h4>Receipt #<?= htmlspecialchars($row['Receipt_ID']) ?></h4>
        <small>Appointment #<?= htmlspecialchars($row['Appointment_ID']) ?></small>
    </div>

    <div class="receipt-body">
        <div class="receipt-row">
            <div class="receipt-label">Customer Name</div>
            <div class="receipt-value"><?= htmlspecialchars($row['Customer_Name'] ?? '-') ?></div>
        </div>
        <div class="receipt-row">
            <div class="receipt-label">Phone Number</div>
            <div class="receipt-value"><?= htmlspecialchars($row['Cust_PhoneNum'] ?? '-') ?></div>
        </div>
        <div class="receipt-row">
            <div class="receipt-label">Services</div>
            <div class="receipt-value"><?= htmlspecialchars($row['Services'] ?? '-') ?></div>
        </div>
        <div class="receipt-row">
            <div class="receipt-label">Payment Type</div>
            <div class="receipt-value"><?= htmlspecialchars($row['Payment_Method'] ?? '-') ?></div>
        </div>
        <div class="receipt-row">
            <div class="receipt-label">Issued Date</div>
            <div class="receipt-value"><?= htmlspecialchars($row['Issued_Date'] ?? '-') ?></div>
        </div>

        <div class="receipt-row total-row">
            <div class="receipt-label">Total Amount</div>
            <div class="receipt-value">RM <?= htmlspecialchars(number_format($row['Total_amount'] ?? 0, 2)) ?></div>
        </div>

        <div class="text-center mt-4">
            <button class="print-btn" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Receipt
            </button>
        </div>
    </div>
</div>

</body>
</html>
