<?php

include("../config/database.php");

$customer_name = $_POST['customer_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$address = $_POST['address'];

$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];


// CHECK PASSWORD

if($password != $confirm_password)
{
    echo "<script>
    alert('Password and Confirm Password do not match!');
    window.history.back();
    </script>";
    exit();
}


// CHECK EMAIL EXIST

$check_email = mysqli_query($conn,

"SELECT * FROM customer
WHERE Email='$email'");

if(mysqli_num_rows($check_email) > 0)
{
    echo "<script>
    alert('Email already registered!');
    window.history.back();
    </script>";
    exit();
}


// HASH PASSWORD

$hashed_password = password_hash($password, PASSWORD_DEFAULT);


// INSERT CUSTOMER

$sql = "INSERT INTO customer
(
Customer_Name,
Cust_PhoneNum,
Email,
Address,
Customer_Password
)

VALUES
(
'$customer_name',
'$phone',
'$email',
'$address',
'$hashed_password'
)";


if(mysqli_query($conn,$sql))
{
?>

<!DOCTYPE html>
<html>
<head>

<title>Registration Successful</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
}

.card{
    border:none;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center vh-100 align-items-center">

<div class="col-md-6">

<div class="card">

<div class="card-body text-center p-5">

<h2 class="text-success">
✅ Registration Successful
</h2>

<p class="mt-3">
Your account has been created successfully.
</p>

<div class="mt-4">

<a href="login.php"
class="btn btn-primary">
Login Now
</a>

<a href="../index.php"
class="btn btn-secondary">
Home
</a>

</div>

</div>

</div>

</div>

</div>

</div>

</body>
</html>

<?php
}
else
{
    echo "Registration Failed!";
}
?>