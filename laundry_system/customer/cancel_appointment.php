<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

if(isset($_GET['id']))
{
    $appointment_id = $_GET['id'];

    // Pastikan appointment milik customer dan masih Pending
    $check = mysqli_query($conn, "

    SELECT *

    FROM appointment

    WHERE Appointment_ID='$appointment_id'

    AND Customer_ID='$customer_id'

    AND Appointment_Status='Pending'

    ");

    if(mysqli_num_rows($check) > 0)
    {
        mysqli_query($conn, "

        UPDATE appointment

        SET Appointment_Status='Cancelled'

        WHERE Appointment_ID='$appointment_id'

        ");

        echo "<script>
        alert('Appointment cancelled successfully.');
        window.location='my_appointments.php';
        </script>";
    }
    else
    {
        echo "<script>
        alert('Unable to cancel this appointment.');
        window.location='my_appointments.php';
        </script>";
    }
}
else
{
    header("Location: my_appointments.php");
}

?>