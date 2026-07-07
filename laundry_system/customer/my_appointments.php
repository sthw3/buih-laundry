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

SELECT *

FROM appointment

WHERE Customer_ID='$customer_id'

ORDER BY Appointment_Date DESC,
Appointment_Time DESC

");

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>My Appointments</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:Segoe UI;
}

.container{
    margin-top:40px;
    max-width:950px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.card-header{
    background:#253154;
    color:white;
    font-size:22px;
    font-weight:bold;
}

.badge{
    padding:8px 15px;
    font-size:14px;
}

.btn{
    margin-right:5px;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">
📋 My Appointments
</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>

<th>Date</th>

<th>Time</th>

<th>Remark</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($query))
{

?>

<tr>

<td><?= $row['Appointment_ID']; ?></td>

<td><?= date("d/m/Y",strtotime($row['Appointment_Date'])); ?></td>

<td><?= date("g:i A",strtotime($row['Appointment_Time'])); ?></td>

<td><?= $row['Appointment_Remark']; ?></td>

<td>

<?php

$status=$row['Appointment_Status'];

if($status=="Pending")
{
    echo "<span class='badge bg-warning'>Pending</span>";
}
elseif($status=="Confirmed")
{
    echo "<span class='badge bg-primary'>Confirmed</span>";
}
elseif($status=="Completed")
{
    echo "<span class='badge bg-success'>Completed</span>";
}
else
{
    echo "<span class='badge bg-danger'>Cancelled</span>";
}

?>

</td>

<td>

<a href="track.php?appointment_id=<?= $row['Appointment_ID']; ?>"
class="btn btn-info btn-sm">

Track

</a>

<?php

if($status=="Pending")
{

?>

<a href="cancel_appointment.php?id=<?= $row['Appointment_ID']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Are you sure you want to cancel this appointment?')">

Cancel

</a>

<?php

}

?>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

<a href="dashboard.php"
class="btn btn-secondary">

← Back Dashboard

</a>

</div>

</div>

</div>

</body>
</html>