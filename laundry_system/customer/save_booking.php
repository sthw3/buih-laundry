<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

$appointment_remark = $_POST['appointment_remark'];
$collection_method = $_POST['collection_method'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];

$services = $_POST['services'];

// AUTO ASSIGN STAFF

$result = mysqli_query($conn,
"SELECT Staff_ID
FROM staff
ORDER BY Staff_ID ASC");

$staff_ids = [];

while($staff = mysqli_fetch_assoc($result))
{
    $staff_ids[] = $staff['Staff_ID'];
}

$staff_count = count($staff_ids);

$staff_index = $customer_id % $staff_count;

$assigned_staff = $staff_ids[$staff_index];



// INSERT APPOINTMENT

$sql_appointment = "INSERT INTO appointment
(
Customer_ID,
Staff_ID,
Appointment_Date,
Appointment_Time,
Appointment_Remark,
Collection_Method,
Appointment_Status
)

VALUES
(
'$customer_id',
'$assigned_staff',
'$appointment_date',
'$appointment_time',
'$appointment_remark',
'$collection_method',
'Pending'
)";

mysqli_query($conn, $sql_appointment);

$appointment_id = mysqli_insert_id($conn);


// INSERT SELECTED SERVICES

foreach($services as $service_id)
{
    $sql_service = "INSERT INTO appointment_service
    (
    Appointment_ID,
    Service_ID
    )

    VALUES
    (
    '$appointment_id',
    '$service_id'
    )";

    mysqli_query($conn, $sql_service);
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Booking Successful</title>

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

<h1 class="text-success">
✅ Booking Successful
</h1>

<p class="mt-3">
Your Appointment ID
</p>

<h2 class="text-primary">
#<?php echo $appointment_id; ?>
</h2>

<p class="text-muted">
Please save this ID to track your booking.
</p>

<div class="mt-4">

<a href="track.php?appointment_id=<?php echo $appointment_id; ?>"
class="btn btn-primary">
Track Booking
</a>

<a href="booking.php"
class="btn btn-success">
New Booking
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