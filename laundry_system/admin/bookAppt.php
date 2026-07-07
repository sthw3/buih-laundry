<?php
include("../config/database.php");

/* Get Next Appointment ID */
$result = $conn->query("SELECT MAX(Appointment_ID) AS max_id FROM appointment");
$row = $result->fetch_assoc();
$nextAppointmentID = ($row['max_id'] ?? 0) + 1;

/* Dropdown Data */
$staffs = $conn->query("SELECT Staff_ID, Staff_Name FROM staff");
$services = $conn->query("SELECT Service_ID, Service_Name FROM service");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $customer_name  = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $customer_email = $_POST['customer_email'];
    $customer_address = $_POST['customer_address'];
    $staff_id       = $_POST['staff_id'];
    $date           = $_POST['appointment_date'];
    $time           = $_POST['appointment_time'];
    $remark         = $_POST['remark'];
    $status         = $_POST['status'];
    $service_ids    = $_POST['services'] ?? [];

    /* Insert Customer — now includes phone, email, address */
    $stmtCustomer = $conn->prepare("
        INSERT INTO customer (Customer_Name, Cust_PhoneNum, Email, Address)
        VALUES (?, ?, ?, ?)
    ");
    $stmtCustomer->bind_param("ssss", $customer_name, $customer_phone, $customer_email, $customer_address);
    $stmtCustomer->execute();
    $customer_id = $stmtCustomer->insert_id;

    /* Insert Appointment */
    $stmt = $conn->prepare("
        INSERT INTO appointment
        (Customer_ID, Staff_ID, Appointment_Date, Appointment_Time, Appointment_Remark, Appointment_Status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissss", $customer_id, $staff_id, $date, $time, $remark, $status);
    $stmt->execute();
    $appointment_id = $stmt->insert_id;

    /* Insert Services */
    if (!empty($service_ids)) {
        $stmt2 = $conn->prepare("
            INSERT INTO appointment_service (Appointment_ID, Service_ID)
            VALUES (?, ?)
        ");
        foreach ($service_ids as $service_id) {
            $stmt2->bind_param("ii", $appointment_id, $service_id);
            $stmt2->execute();
        }
    }

    header("Location: manageAppt.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Appointment</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.container {
    max-width: 800px;
    margin-top: 50px;
}
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.card-header {
    background: #253154;
    color: white;
    padding: 20px;
    border-radius: 15px 15px 0 0 !important;
}
.card-body {
    padding: 30px;
}
.form-label {
    font-weight: 600;
}
.btn-save {
    background: #253154;
    color: white;
    border: none;
}
.btn-save:hover {
    background: #1d2744;
    color: white;
}
</style>
</head>

<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Add New Appointment</h3>
        </div>

        <div class="card-body">
            <form method="POST">

                <!-- Appointment ID -->
                <div class="mb-3">
                    <label class="form-label">Appointment ID</label>
                    <input type="text" class="form-control" value="<?= $nextAppointmentID ?>" readonly>
                </div>

                <!-- Customer Name -->
                <div class="mb-3">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>

                <!-- Customer Phone -->
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="customer_phone" class="form-control">
                </div>

                <!-- Customer Email -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="customer_email" class="form-control">
                </div>

                <!-- Customer Address -->
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="customer_address" class="form-control">
                </div>

                <!-- Staff -->
                <div class="mb-3">
                    <label class="form-label">Staff</label>
                    <select name="staff_id" class="form-select" required>
                        <option value="">Select Staff</option>
                        <?php while($s = $staffs->fetch_assoc()): ?>
                            <option value="<?= $s['Staff_ID'] ?>"><?= $s['Staff_Name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label">Appointment Date</label>
                    <input type="date" name="appointment_date" class="form-control" required>
                </div>

                <!-- Time -->
                <div class="mb-3">
                    <label class="form-label">Appointment Time</label>
                    <input type="time" name="appointment_time" class="form-control" required>
                </div>

                <!-- Remark -->
                <div class="mb-3">
                    <label class="form-label">Remark</label>
                    <textarea name="remark" class="form-control" rows="3"></textarea>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Services -->
                <div class="mb-3">
                    <label class="form-label">Services</label>
                    <select name="services[]" class="form-select" multiple required>
                        <?php while($srv = $services->fetch_assoc()): ?>
                            <option value="<?= $srv['Service_ID'] ?>"><?= $srv['Service_Name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    
                </div>

                <div class="d-flex gap-2">
                   
                       <button type="submit" class="btn btn-save">Save Appointment</button>

                    <a href="manageAppt.php" class="btn btn-secondary">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>