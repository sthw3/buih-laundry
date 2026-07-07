<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Track Booking</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f3f4f6;
    margin: 0;
    padding: 40px 20px;
}

/* Container utama mirip seperti layout card di gambar */
.container {
    max-width: 850px;
    margin: auto;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden; /* Supaya header melengkung sempurna */
}

/* Header Banner Navy Solid seperti di gambar */
.form-header {
    background: #253154;
    padding: 24px 32px;
}

.form-header h2 {
    color: white;
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    text-align: left;
}

/* Area Form dalam */
.form-body {
    padding: 32px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
    font-size: 15px;
}

/* Input Elegan sesuai Gambar */
input[type="number"] {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 15px;
    color: #4b5563;
    background-color: #ffffff;
    outline: none;
    transition: border-color 0.2s;
}

input[type="number"]:focus {
    border-color: #253154;
}

/* Tombol di Kiri Bawah mirip Gambar */
.button-group {
    display: flex;
    gap: 10px;
    margin-top: 24px;
    justify-content: flex-start;
}

button {
    background: #253154;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    transition: background 0.2s;
}

button:hover {
    background: #1e2744;
}

.home-btn {
    background: #6b7280;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 6px;
    display: inline-block;
    font-size: 15px;
    font-weight: 500;
    transition: background 0.2s;
}

.home-btn:hover {
    background: #4b5563;
}

.back-btn{
    background:white;
    color:#253154;
    text-decoration:none;
    padding:8px 18px;
    border-radius:8px;
    font-weight:600;
    transition:.3s;
}

.back-btn:hover{
    background:#f5f5f5;
}

hr {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 30px 0;
}

/* Card Hasil Booking */
.booking-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    padding: 28px;
    border-radius: 12px;
}

.booking-card h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #111827;
    font-size: 18px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 8px;
}

.detail-row {
    display: flex;
    margin-bottom: 14px;
    font-size: 15px;
}

.detail-label {
    width: 200px;
    font-weight: 600;
    color: #4b5563;
}

.detail-value {
    color: #111827;
}

.status {
    padding: 4px 12px;
    border-radius: 20px;
    color: white;
    font-size: 13px;
    font-weight: bold;
}

.pending { background: #f59e0b; }
.confirmed { background: #3b82f6; }
.completed { background: #22c55e; }
.cancelled { background: #ef4444; }

.services {
    background: white;
    padding: 16px;
    border-radius: 8px;
    margin-top: 10px;
    border: 1px solid #e5e7eb;
    line-height: 1.8;
}

.total {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
    font-size: 24px;
    font-weight: bold;
    color: #16a34a;
}

.not-found {
    text-align: center;
    color: #ef4444;
    font-weight: bold;
    padding: 20px;
    background: #fef2f2;
    border-radius: 8px;
    border: 1px solid #fca5a5;
}
</style>
</head>
<body>

<div class="container">

    <div class="form-header">

    <div style="display:flex;
                justify-content:space-between;
                align-items:center;">

        <h2 style="margin:0;">
            🧺 Track Booking Status
        </h2>

        <a href="view_appointment.php"
        class="back-btn">

            <i>⬅</i> Back

        </a>

    </div>

</div>


<div class="form-body">


<?php


if(isset($_GET['appointment_id'])) {

            
     $appointment_id = mysqli_real_escape_string($conn, $_GET['appointment_id']);

             $query = mysqli_query($conn, "

                SELECT
                appointment.*,
                customer.Customer_Name,
                customer.Cust_PhoneNum,
                staff.Staff_Name

                FROM appointment

                INNER JOIN customer
                ON appointment.Customer_ID = customer.Customer_ID

                INNER JOIN staff
                ON appointment.Staff_ID = staff.Staff_ID

                WHERE
                appointment.Appointment_ID='$appointment_id'

                AND
                appointment.Customer_ID='$customer_id'

                ");

            if(mysqli_num_rows($query) > 0) {
                $row = mysqli_fetch_assoc($query);
        ?>
                <hr>
                <div class="booking-card">
                    <h3>📋 Booking Details</h3>
                    
                    <div class="detail-row">
                        <div class="detail-label">Customer Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['Customer_Name']); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Phone Number</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['Cust_PhoneNum']); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Appointment Date</div>
                        <div class="detail-value"><?php echo date("d F Y", strtotime($row['Appointment_Date'])); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Appointment Time</div>
                        <div class="detail-value"><?php echo date("g:i A", strtotime($row['Appointment_Time'])); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Appointment Remark</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['Appointment_Remark']); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label"> Collection Method</div>
                        <div class="detail-value"><?php  if($row['Collection_Method']=="Pickup")
                            {
                                echo "🚗 Self Pickup";
                            }
                            else
                            {
                                echo "🚚 Home Delivery";
                            }

                            ?>
                        </div>
                    </div>


                    <div class="detail-row">
                        <div class="detail-label">Assigned Staff</div>
                        <div class="detail-value">
                            👤 <?php echo htmlspecialchars($row['Staff_Name']); ?>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <?php
                            $status = $row['Appointment_Status'];
                            if($status == "Pending") echo "<span class='status pending'>🟡 Pending</span>";
                            elseif($status == "Confirmed") echo "<span class='status confirmed'>🔵 Confirmed</span>";
                            elseif($status == "Completed") echo "<span class='status completed'>🟢 Completed</span>";
                            elseif($status == "Cancelled") echo "<span class='status cancelled'>🔴 Cancelled</span>";
                            else echo htmlspecialchars($status);
                            ?>
                        </div>
                    </div>

                    <h3 style="margin-top: 25px;">🧼 Selected Services</h3>
                    <div class="services">
                        <?php
                        $total_price = 0;
                        $service_query = mysqli_query($conn, "
                            SELECT service.Service_Name, service.Price 
                            FROM appointment_service 
                            INNER JOIN service ON appointment_service.Service_ID = service.Service_ID 
                            WHERE appointment_service.Appointment_ID = '$appointment_id'
                        ");

                        while($service = mysqli_fetch_assoc($service_query)) {
                            echo "✓ " . htmlspecialchars($service['Service_Name']) . " (RM" . number_format($service['Price'], 2) . ")<br>";
                            $total_price += $service['Price'];
                        }
                        ?>
                    </div>

                    <div class="total">
                        💰 Total Price: RM <?php echo number_format($total_price, 2); ?>
                    </div>
                </div>



        <?php
            } else {
                echo "<hr><div class='not-found'>
                ❌ Booking not found or you do not have permission to view this appointment.
                </div>";
            }
        }
        ?>
    </div>
</div>

</body>
</html>