<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

$query = mysqli_query($conn, "

SELECT
appointment.*,
staff.Staff_Name

FROM appointment

INNER JOIN staff
ON appointment.Staff_ID = staff.Staff_ID

WHERE appointment.Customer_ID='$customer_id'

ORDER BY appointment.Appointment_Date DESC

");

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>My Appointments</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:1200px;
    margin:50px auto;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}

.card-header{
    background:#253154;
    color:white;
    padding:20px;
    border-radius:15px 15px 0 0 !important;
}

.card-body{
    padding:30px;
}

.table{
    margin-bottom:0;
}

.table thead{
    background:#253154;
    color:white;
}

.table th{
    font-size:14px;
    font-weight:600;
    letter-spacing:.3px;
    padding:15px;
    text-align:center;
}

.table td{
    padding:15px;
    vertical-align:middle;
}

.table tbody tr:hover{
    background:#f8fafc;
}


.status-badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
    white-space:nowrap;
}

.pending{
    background:#ffc107;
    color:#212529;
}

.confirmed{
    background:#0d6efd;
    color:#fff;
}

.completed{
    background:#198754;
    color:#fff;
}

.cancelled{
    background:#dc3545;
    color:#fff;
}

.action-buttons{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.action-buttons .btn{
    min-width:105px;
    font-size:13px;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<h3>

📋 Appointment History

</h3>

</div>

<div class="card-body">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>Appointment ID</th>

<th>Date</th>

<th>Time</th>

<th>Remark</th>

<th>Assigned Staff</th>

<th style="width:150px;">Status</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($query))
{

$status = strtolower($row['Appointment_Status']);

switch($status)
{
    case "pending":
        $status = "<span class='status-badge pending'>Pending</span>";
        break;

    case "confirmed":
        $status = "<span class='status-badge confirmed'>Confirmed</span>";
        break;

    case "completed":
        $status = "<span class='status-badge completed'>Completed</span>";
        break;

    case "cancelled":
        $status = "<span class='status-badge cancelled'>Cancelled</span>";
        break;

    default:
        $status = "<span class='status-badge'>Unknown</span>";
}

?>

<tr>

<td>

<div class="action-buttons">

<?php echo $row['Appointment_ID']; ?>

</td>

<td>

<?php echo date("d/m/Y",strtotime($row['Appointment_Date'])); ?>

</td>

<td>

<?php echo date("g:i A",strtotime($row['Appointment_Time'])); ?>

</td>

<td>

<?php echo $row['Appointment_Remark']; ?>

</td>

<td>

<?php echo $row['Staff_Name']; ?>

</td>

<td>

<?php echo $status; ?>

</td>

<td>

<a
href="track.php?appointment_id=<?php echo $row['Appointment_ID']; ?>"
class="btn btn-primary btn-sm">

View Details

</a>

<?php

if($row['Appointment_Status']=="Completed")
{

?>

<a
href="receipt.php?appointment_id=<?php echo $row['Appointment_ID']; ?>"
class="btn btn-success btn-sm">

Receipt

</a>

<?php

}

?>

<?php

if($row['Appointment_Status']=="Pending")
{

?>

<a
href="cancel_appointment.php?id=<?php echo $row['Appointment_ID']; ?>"
class="btn btn-danger btn-sm"

onclick="return confirm('Are you sure you want to cancel this appointment?');">

Cancel

</a>

<?php

}

?>

</div>

</td>

</tr>

<?php
}
?>

</tbody>

</table>

<div class="mt-3">

<a

href="dashboard.php"

class="btn btn-secondary">

Back

</a>

</div>

</div>

</div>

</div>

</body>

</html>