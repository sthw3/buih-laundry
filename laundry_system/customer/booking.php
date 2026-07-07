<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

/* Get Customer Information */

$customer = mysqli_query($conn,

"SELECT *

FROM customer

WHERE Customer_ID='$customer_id'");

$data = mysqli_fetch_assoc($customer);

/* Get Next Appointment ID */

$result = mysqli_query($conn,

"SELECT MAX(Appointment_ID) AS max_id

FROM appointment");

$row = mysqli_fetch_assoc($result);

$nextAppointmentID = ($row['max_id'] ?? 0) + 1;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Laundry Booking Form</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

<style>
body{
    background:#f5f7fa;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container{
    max-width:800px;
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

.form-label{
    font-weight:600;
}

.btn-book{
    background:#253154;
    color:white;
}

.btn-book:hover{
    background:#1d2744;
    color:white;
}
</style>

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="card-header">
            <h3>🧺 Laundry Booking Form</h3>
        </div>

        <div class="card-body">

            <form action="save_booking.php" method="POST">

            <div class="mb-3">
    <label class="form-label">Appointment ID</label>
    <input type="text"
           class="form-control"
           value="<?= $nextAppointmentID; ?>"
           readonly>
</div>

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text"
                        name="customer_name"
                        class="form-control"
                        value="<?= $data['Customer_Name']; ?>"
                        readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text"
                        name="phone"
                        class="form-control"
                        value="<?= $data['Cust_PhoneNum']; ?>"
                        readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                        name="email"
                        class="form-control"
                        value="<?= $data['Email']; ?>"
                        readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea
                            name="address"
                            class="form-control"
                            rows="3"
                            readonly><?= $data['Address']; ?></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label"> Appointment Remark</label>
                  <input type="text"
                         name="appointment_remark"
                         class="form-control"
                         placeholder="Example: Wash school uniform">
                </div>

                <div class="mb-3">

                    <label class="form-label">
                    Collection Method
                    </label>

                    <div class="form-check">

                    <input
                    class="form-check-input"
                    type="radio"
                    name="collection_method"
                    value="Pickup"
                    checked>

                    <label class="form-check-label">
                    🚗 Self Pickup
                    </label>

                    </div>

                    <div class="form-check">

                    <input
                    class="form-check-input"
                    type="radio"
                    name="collection_method"
                    value="Delivery">

                    <label class="form-check-label">
                    🚚 Home Delivery
                    </label>

                    </div>

                    </div>
            


                <div class="row">

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                Appointment Date
                            </label>

                            <input type="date"
                                   name="appointment_date"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                Appointment Time
                            </label>

                            <input type="time"
                                   name="appointment_time"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                </div>

                <hr>

                <h5 class="mb-3">Select Services</h5>

                <?php

                $services = mysqli_query($conn,
                "SELECT * FROM service");

                while($row = mysqli_fetch_assoc($services))
                {
                ?>

                <div class="form-check mb-2">

                    <input class="form-check-input"
                           type="checkbox"
                           name="services[]"
                           value="<?= $row['Service_ID']; ?>">

                    <label class="form-check-label">
                        <?= $row['Service_Name']; ?>
                        (RM<?= $row['Price']; ?>)
                    </label>

                </div>

                <?php
                }
                ?>

                <div class="d-flex gap-2 mt-4">

                    <button type="submit"
                            class="btn btn-book">
                        Book Appointment
                    </button>

                    <a href="dashboard.php"
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