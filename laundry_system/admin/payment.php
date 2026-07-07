<?php

include("../config/database.php");

if(!isset($_GET['id']))
{
    die("Invalid Appointment.");
}

$appointment_id = (int)$_GET['id'];

/* Appointment Info */
$appointment = mysqli_query($conn,"
SELECT
appointment.*,
customer.Customer_Name,
customer.Cust_PhoneNum
FROM appointment
INNER JOIN customer
ON appointment.Customer_ID = customer.Customer_ID
WHERE appointment.Appointment_ID='$appointment_id'
");

if(mysqli_num_rows($appointment)==0)
{
    die("Appointment not found.");
}

$appt = mysqli_fetch_assoc($appointment);

/* Payment List */
$payments = mysqli_query($conn,"
SELECT *
FROM payment
");

/* Selected Services */
$services = mysqli_query($conn,"
SELECT
service.Service_Name,
service.Price
FROM appointment_service

INNER JOIN service
ON appointment_service.Service_ID = service.Service_ID

WHERE Appointment_ID='$appointment_id'
");

$total = 0;

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Payment</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI';
}

.container{
    max-width:700px;
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
}

.card-body{
    padding:30px;
}

.table td{
    vertical-align:middle;
}

.total{
    font-size:24px;
    font-weight:bold;
    color:green;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<h3>

Payment

</h3>

</div>

<div class="card-body">

<form action="savePayment.php" method="POST">

<input
type="hidden"
name="appointment_id"
value="<?php echo $appointment_id; ?>">

<div class="mb-3">

<label class="form-label">

Customer Name

</label>

<input
type="text"
class="form-control"
value="<?php echo $appt['Customer_Name']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Phone Number

</label>

<input
type="text"
class="form-control"
value="<?php echo $appt['Cust_PhoneNum']; ?>"
readonly>

</div>

<h5 class="mt-4 mb-3">

Selected Services

</h5>

<table class="table table-bordered">

<thead>

<tr>

<th>Service</th>

<th width="150">Price (RM)</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($services))
{

$total += $row['Price'];

?>

<tr>

<td>

<?php echo $row['Service_Name']; ?>

</td>

<td>

RM <?php echo number_format($row['Price'],2); ?>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

<div class="mb-4 total">

Total : RM <?php echo number_format($total,2); ?>

</div>

<div class="mb-4">

<label class="form-label">

Payment Method

</label>

<select
name="payment_id"
class="form-select"
required>

<option value="">

-- Select Payment Method --

</option>

<?php

while($payment=mysqli_fetch_assoc($payments))
{

?>

<option value="<?php echo $payment['Payment_ID']; ?>">

<?php echo $payment['Payment_Method']; ?>

</option>

<?php

}

?>

</select>

</div>

<div class="d-flex gap-2">

<button
type="submit"
class="btn btn-success">

Confirm Payment

</button>

<a
href="manageAppt.php"
class="btn btn-secondary">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</body>

</html>