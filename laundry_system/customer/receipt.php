<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

if($appointment_id <= 0)
{
    die("Invalid appointment.");
}

$query = "

SELECT

receipt.*,
appointment.Appointment_ID,
appointment.Collection_Method,

customer.Customer_Name,
customer.Cust_PhoneNum,
payment.Payment_Method,

GROUP_CONCAT(service.Service_Name SEPARATOR ', ') AS Services

FROM receipt

INNER JOIN appointment
ON receipt.Appointment_ID = appointment.Appointment_ID

INNER JOIN customer
ON appointment.Customer_ID = customer.Customer_ID

LEFT JOIN payment
ON receipt.Payment_ID = payment.Payment_ID

LEFT JOIN appointment_service
ON appointment.Appointment_ID = appointment_service.Appointment_ID

LEFT JOIN service
ON appointment_service.Service_ID = service.Service_ID

WHERE

appointment.Appointment_ID = ?

AND

appointment.Customer_ID = ?

AND

appointment.Appointment_Status='Completed'

GROUP BY receipt.Receipt_ID

";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii",$appointment_id,$customer_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows==0)
{
    die("Receipt not found.");
}

$row = $result->fetch_assoc();

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Receipt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f5f7fa;
font-family:'Segoe UI';
padding:40px;
}

.card{
max-width:700px;
margin:auto;
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

.row-item{
display:flex;
justify-content:space-between;
padding:12px 0;
border-bottom:1px solid #eee;
}

.total{
font-size:24px;
font-weight:bold;
color:green;
margin-top:20px;
}


@media print{

body{
    background:white;
    padding:0;
}

.btn{
    display:none;
}

.card{
    box-shadow:none;
    border:1px solid #ddd;
}

}
</style>

</head>

<body>

<div class="card">

<div class="card-header">

<h3>

🧾 Receipt #<?php echo $row['Receipt_ID']; ?>

</h3>

</div>

<div class="card-body">

<div class="row-item">
<strong>Appointment ID</strong>
<span>#<?php echo $row['Appointment_ID']; ?></span>
</div>

<div class="row-item">
<strong>Customer Name</strong>
<span><?php echo $row['Customer_Name']; ?></span>
</div>

<div class="row-item">
<strong>Phone Number</strong>
<span><?php echo $row['Cust_PhoneNum']; ?></span>
</div>


<div class="row-item">
<strong>Collection Method</strong>
<span>

<?php

if($row['Collection_Method']=="Pickup")
{
    echo "🚗 Self Pickup";
}
else
{
    echo "🚚 Home Delivery";
}

?>

</span>
</div>

<div class="row-item">
<strong>Services</strong>
<span><?php echo $row['Services']; ?></span>
</div>

<div class="row-item">
<strong>Payment Method</strong>
<span><?php echo $row['Payment_Method']; ?></span>
</div>

<div class="row-item">
<strong>Issued Date</strong>
<span><?php echo date("d/m/Y",strtotime($row['Issued_Date'])); ?></span>
</div>

<div class="total">

RM <?php echo number_format($row['Total_Amount'],2); ?>

</div>

<br>

<div class="d-flex gap-2">

<a href="view_appointment.php" class="btn btn-secondary">
⬅ Back
</a>

<button
class="btn btn-success"
onclick="window.print()">

🖨 Print Receipt

</button>

</div>

</div>

</div>

</body>

</html>