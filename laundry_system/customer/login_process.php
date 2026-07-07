<?php

session_start();
include("../config/database.php");

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM customer
WHERE Email = '$email'";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 1)
{
    $customer = mysqli_fetch_assoc($result);

    if(password_verify($password, $customer['Customer_Password']))
    {
        $_SESSION['customer_id'] = $customer['Customer_ID'];
        $_SESSION['customer_name'] = $customer['Customer_Name'];

        header("Location: dashboard.php");
        exit();
    }
    else
    {
        echo "<script>
        alert('Incorrect Password!');
        window.location='login.php';
        </script>";
    }
}
else
{
    echo "<script>
    alert('Email not found!');
    window.location='login.php';
    </script>";
}

?>