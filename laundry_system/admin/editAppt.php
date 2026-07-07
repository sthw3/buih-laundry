<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No appointment ID provided.");
}
$id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("UPDATE appointment SET Appointment_Date = ?, Appointment_Time = ?, Appointment_Remark = ?, Appointment_Status = ?, Staff_ID = ? WHERE Appointment_ID = ?");
    $stmt->bind_param("ssssii", $_POST['date'], $_POST['time'], $_POST['remark'], $_POST['status'], $_POST['staff_id'], $id);
    $stmt->execute();




    if($_POST['status']=="Completed") {
    $payment_id = $_POST['payment_id'];

    // Calculate total price
    $total = 0;
    $priceQuery = mysqli_query($conn,"
    SELECT service.Price
    FROM appointment_service
    INNER JOIN service
    ON appointment_service.Service_ID = service.Service_ID
    WHERE Appointment_ID='$id'
    ");

        while($price = mysqli_fetch_assoc($priceQuery)) {
        $total += $price['Price'];
    }

    // Check receipt already exists
    $check = mysqli_query($conn,"
    SELECT *
    FROM receipt
    WHERE Appointment_ID='$id'
    ");

    if(mysqli_num_rows($check)==0)
    {

        mysqli_query($conn,"
        INSERT INTO receipt
        (
            Appointment_ID,
            Payment_ID,
            Total_Amount,
            Issued_Date
        )

        VALUES
        (
            '$id',
            '$payment_id',
            '$total',
            CURDATE()
        )
        ");

    }

}

    // Update Services (Delete old, Insert new)
    $del = $conn->prepare("DELETE FROM appointment_service WHERE Appointment_ID = ?");
    $del->bind_param("i", $id);
    $del->execute();

    if (!empty($_POST['services'])) {
        $ins = $conn->prepare("INSERT INTO appointment_service (Appointment_ID, Service_ID) VALUES (?, ?)");
        foreach ($_POST['services'] as $service_id) {
            $service_id = (int) $service_id;
            $ins->bind_param("ii", $id, $service_id);
            $ins->execute();
        }
    }

    if($_POST['status']=="Completed")
{
    echo "<script>

    alert('Appointment Updated.');

    window.location='payment.php?id=$id';

    </script>";
}
else
{
    echo "<script>

    alert('Appointment Updated.');

    window.location='manageAppt.php';

    </script>";
}

exit();
}

$stmt = $conn->prepare("
    SELECT 
        appointment.*,
        customer.Customer_Name,
        customer.Cust_PhoneNum,
        staff.Staff_Name
    FROM appointment
    INNER JOIN customer ON appointment.Customer_ID = customer.Customer_ID
    INNER JOIN staff ON appointment.Staff_ID = staff.Staff_ID
    WHERE appointment.Appointment_ID = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();

if (!$appt) {
    die("Appointment not found.");
}

$svcStmt = $conn->prepare("SELECT Service_ID FROM appointment_service WHERE Appointment_ID = ?");
$svcStmt->bind_param("i", $id);
$svcStmt->execute();
$selectedServices = array_column($svcStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'Service_ID');

$allServices = $conn->query("SELECT * FROM service");
$allStaff = $conn->query("SELECT * FROM staff");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Appointment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body { background:#f5f7fa; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
.form-container {
    max-width: 900px;
    margin: 50px auto;
    background: #fff;
    padding: 35px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.form-container h4 { color: #253154; margin-bottom: 25px; }
.btn-primary { background:#253154; border-color:#253154; }
.btn-primary:hover { background:#1a2340; border-color:#1a2340; }
</style>
</head>
<body>

<div class="form-container">
    <h4>Edit Appointment #<?= htmlspecialchars($id) ?></h4>

    <form method="POST">
        <div class="row">
            <div class="mb-3 col-md-6">
                <label>Customer Name</label>
                <input type="text" class="form-control"
                    value="<?= htmlspecialchars($appt['Customer_Name']) ?>" disabled>
            </div>

            <div class="mb-3 col-md-6">
                <label>Phone Number</label>
                <input type="text" class="form-control"
                    value="<?= htmlspecialchars($appt['Cust_PhoneNum']) ?>" disabled>
            </div>

            <div class="mb-3 col-md-6">
                <label>Staff</label>
                <select name="staff_id" class="form-select" required>
                    <?php while ($staff = $allStaff->fetch_assoc()): ?>
                        <option value="<?= $staff['Staff_ID'] ?>"
                            <?= $staff['Staff_ID'] == $appt['Staff_ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($staff['Staff_Name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3 col-md-6">
                <label>Date</label>
                <input type="date" name="date" class="form-control"
                    value="<?= htmlspecialchars($appt['Appointment_Date']) ?>" required>
            </div>

            <div class="mb-3 col-md-6">
                <label>Time</label>
                <input type="time" name="time" class="form-control"
                    value="<?= htmlspecialchars($appt['Appointment_Time']) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Remark</label>
            <textarea name="remark" class="form-control" rows="3"><?= htmlspecialchars($appt['Appointment_Remark']) ?></textarea>
        </div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
id="status"
class="form-select"
required>

<?php

$statuses=['Pending','Confirmed','Completed','Cancelled'];

foreach($statuses as $s):

?>

<option
value="<?= $s ?>"
<?= strtolower($appt['Appointment_Status'])==strtolower($s) ? 'selected':'' ?>>

<?= $s ?>

</option>

<?php endforeach; ?>

</select>

</div>




        <div class="mb-4">
            <label>Services</label>
            <select name="services[]" class="form-select" multiple required>
                <?php while($srv = $allServices->fetch_assoc()): ?>
                    <option value="<?= $srv['Service_ID'] ?>"
                        <?= in_array($srv['Service_ID'], $selectedServices) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($srv['Service_Name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="manageAppt.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>

function togglePayment(){

    let status=document.getElementById("status").value;

    let payment=document.getElementById("paymentSection");

    if(status=="Completed")
    {
        payment.style.display="block";
    }
    else
    {
        payment.style.display="none";
    }

}

document.getElementById("status").addEventListener("change",togglePayment);

window.onload=togglePayment;

</script>


</body>
</html>