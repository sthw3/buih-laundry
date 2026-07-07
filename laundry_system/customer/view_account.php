<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

$query = mysqli_query($conn,

"SELECT *
FROM customer
WHERE Customer_ID='$customer_id'");

$customer = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>View Account</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:750px;
    margin-top:50px;
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

.table td{
    padding:15px;
    vertical-align:middle;
}

.table td:first-child{
    width:220px;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<h3>
👤 My Account
</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<td>Customer ID</td>

<td><?php echo $customer['Customer_ID']; ?></td>

</tr>

<tr>

<td>Name</td>

<td><?php echo $customer['Customer_Name']; ?></td>

</tr>

<tr>

<td>Phone Number</td>

<td><?php echo $customer['Cust_PhoneNum']; ?></td>

</tr>

<tr>

<td>Email</td>

<td><?php echo $customer['Email']; ?></td>

</tr>

<tr>

<td>Address</td>

<td><?php echo $customer['Address']; ?></td>

</tr>

</table>

<div class="mt-4">

<a href="update_account.php"
class="btn btn-primary">

Update Account

</a>

<a href="dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

</div>

</div>

</div>

</body>

</html>