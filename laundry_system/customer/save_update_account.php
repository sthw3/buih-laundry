<?php

session_start();

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

$customer_name = $_POST['customer_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$address = $_POST['address'];

$sql = "UPDATE customer

SET

Customer_Name='$customer_name',

Cust_PhoneNum='$phone',

Email='$email',

Address='$address'

WHERE Customer_ID='$customer_id'";

if(mysqli_query($conn,$sql))
{
    $_SESSION['customer_name'] = $customer_name;

    echo "<script>

    alert('Account Updated Successfully!');

    window.location='view_account.php';

    </script>";
}
else
{
    echo "Update Failed!";
}

?>