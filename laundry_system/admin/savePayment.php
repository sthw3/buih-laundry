<?php

include("../config/database.php");

if($_SERVER['REQUEST_METHOD']!="POST")
{
    die("Invalid Request.");
}

$appointment_id = (int)$_POST['appointment_id'];
$payment_id = (int)$_POST['payment_id'];

/* Check payment selected */

if($payment_id<=0)
{
    die("Please select payment method.");
}

/* Check receipt already exists */

$check=mysqli_query($conn,"
SELECT *
FROM receipt
WHERE Appointment_ID='$appointment_id'
");

if(mysqli_num_rows($check)>0)
{
    echo "<script>
    alert('Receipt already exists.');
    window.location='manageReceipt.php';
    </script>";
    exit();
}

/* Calculate Total Price */

$total=0;

$priceQuery=mysqli_query($conn,"

SELECT
service.Price

FROM appointment_service

INNER JOIN service
ON appointment_service.Service_ID=service.Service_ID

WHERE Appointment_ID='$appointment_id'

");

while($row=mysqli_fetch_assoc($priceQuery))
{
    $total += $row['Price'];
}

/* Insert Receipt */

$insert=mysqli_query($conn,"

INSERT INTO receipt
(
    Appointment_ID,
    Payment_ID,
    Total_Amount,
    Issued_Date
)

VALUES
(
    '$appointment_id',
    '$payment_id',
    '$total',
    CURDATE()
)

");

if($insert)
{
    echo "<script>

    alert('Payment Successful. Receipt Generated.');

    window.location='manageReceipt.php';

    </script>";
}
else
{
    die("Error : ".mysqli_error($conn));
}

?>