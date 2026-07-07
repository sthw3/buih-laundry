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

receipt.Receipt_ID,
receipt.Total_amount,
receipt.Issued_Date,

appointment.Appointment_ID,
appointment.Appointment_Status

FROM receipt

INNER JOIN appointment
ON receipt.Appointment_ID = appointment.Appointment_ID

WHERE
appointment.Customer_ID='$customer_id'

AND
appointment.Appointment_Status='Completed'

ORDER BY receipt.Issued_Date DESC

");

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>View Receipt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:900px;
    margin-top:50px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,.1);
}

.card-header{
    background:#253154;
    color:white;
    padding:20px;
    border-radius:15px 15px 0 0!important;
}

.card-body{
    padding:30px;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<h3>🧾 My Receipts</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>Receipt ID</th>

<th>Appointment ID</th>

<th>Issued Date</th>

<th>Total Amount</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($query))
{

?>

<tr>

<td>#<?php echo $row['Receipt_ID']; ?></td>

<td>#<?php echo $row['Appointment_ID']; ?></td>

<td><?php echo date("d/m/Y",strtotime($row['Issued_Date'])); ?></td>

<td>RM <?php echo number_format($row['Total_amount'],2); ?></td>

<td>

<a href="receipt.php?id=<?php echo $row['Receipt_ID']; ?>"

class="btn btn-primary btn-sm">

View

</a>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

<a href="dashboard.php" class="btn btn-secondary">

⬅ Back

</a>

</div>

</div>

</div>

</body>

</html>