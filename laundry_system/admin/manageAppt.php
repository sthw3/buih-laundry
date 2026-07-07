<?php
include("../config/database.php");

$query = "SELECT 
    appointment.Appointment_ID,
    appointment.Appointment_Date,
    appointment.Appointment_Time,
    appointment.Appointment_Remark,
    appointment.Collection_Method,
    appointment.Appointment_Status,

    customer.Customer_Name,
    customer.Cust_PhoneNum,

    staff.Staff_Name,

    GROUP_CONCAT(service.Service_Name SEPARATOR ', ') AS Services

FROM appointment

INNER JOIN customer 
    ON appointment.Customer_ID = customer.Customer_ID

INNER JOIN staff 
    ON appointment.Staff_ID = staff.Staff_ID

LEFT JOIN appointment_service 
    ON appointment.Appointment_ID = appointment_service.Appointment_ID

LEFT JOIN service 
    ON appointment_service.Service_ID = service.Service_ID

GROUP BY appointment.Appointment_ID";

$result = $conn->query($query);

if(!$result){
    die("Query failed: " .$conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Appointment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>

:root {
    --brand:      #253154;
    --brand-lite: #2e3d66;
    --accent:     #2dd4bf;   /* teal highlight */
    --accent2:    #6c8ebf;   /* lighter blue   */
    --bg:         #f0f2f7;
    --white:      #ffffff;
    --text-dark:  #1a2340;
    --text-muted: #6b7a99;
    --radius-lg:  16px;
    --radius-md:  12px;
    --shadow-sm:  0 2px 8px rgba(37,49,84,.08);
    --shadow-md:  0 6px 24px rgba(37,49,84,.12);
}

body{
    background:#f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.wrapper {
    display: flex;
    width: 100%;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 260px;
    background: var(--brand);
    padding: 28px 18px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 8px 28px;
    border-bottom: 1px solid rgba(255,255,255,.1);
    margin-bottom: 24px;
}

.logo-bubble {
    width: 42px; height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
}

.logo-text { line-height: 1.1; }
.logo-name  { font-weight: 700; font-size: 1rem; color: #fff; letter-spacing: .3px; }
.logo-sub   { font-size: .7rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; }

.nav-label {
    font-size: .68rem;
    font-weight: 600;
    color: rgba(255,255,255,.35);
    text-transform: uppercase;
    letter-spacing: 1.2px;
    padding: 0 10px;
    margin: 18px 0 8px;
}

.nav-link-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    color: rgba(255,255,255,.75);
    text-decoration: none;
    font-size: .9rem;
    font-weight: 500;
    transition: background .18s, color .18s;
    margin-bottom: 2px;
}

.nav-link-item:hover,
.nav-link-item.active {
    background: rgba(255,255,255,.12);
    color: #fff;
}

.nav-link-item.active { background: rgba(45,212,191,.18); color: var(--accent); }

.nav-link-item i { font-size: 1.05rem; width: 20px; text-align: center; }

.sidebar-footer {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,.1);
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    color: rgba(255,255,255,.6);
    text-decoration: none;
    font-size: .88rem;
    transition: background .18s, color .18s;
}

.logout-btn:hover { 
    background: rgba(220,53,69,.2); color: #ff6b6b; 
}

.section-title {
    font-size: 0.8rem;
    color: #d7ccc8;
    margin-bottom: 10px;
    text-transform: uppercase;
}


.btn-action { 
    padding: 5px 12px; 
    border-radius: 6px; 
    text-decoration: none; 
    font-size: 0.85rem; 
}

.edit-btn { 
    background: #e3f2fd; 
    color: #1976d2; 
}

.delete-btn { 
    background: #ffebee;
    color: #d32f2f; 
}

.main-content {
    flex-grow: 1;
    background: #fff;
    padding: 40px;
}

.table-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    background: #fff;
    
}

.table {
    margin-bottom: 0;
}

.table thead {
    background-color: #f8f9fa;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 1px;
    color: #6c757d;
}

.table td, .table th{
    padding: 1.25rem;
    vertical-align: middle;
}

.btn-action {
    padding: 5px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.85rem;
    margin-right: 5px;
}

.dashboard-header{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 20px 25px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.notification-btn{
    position: relative;
    font-size: 1.3rem;
    cursor: pointer;
    background: transparent;
    border: none;
    outline: none;
    padding: 5px;    
}

.notification-badge{
    position:absolute;
    top:-5px;
    right:-5px;
    background:#dc3545;
    color:white;
    border-radius:50%;
    width:20px;
    height:20px;
    font-size:0.7rem;
    display:flex;
    align-items:center;
    justify-content:center;
}

.admin-profile{
    display:flex;
    align-items:center;
    gap:12px;
}

.avatar{
     width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #253154;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-info{
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.admin-name{
    font-weight: 600;
    color: #253154;
}

.admin-role{
    font-size: 0.85rem;
    color: #6c757d;
}

.collection-method
{
    display:inline-flex;
    align-items:center;
    gap:6px;
    color:#253154;
    font-weight:600;
}

</style>
</head>
<body>
<div class="wrapper">
    <!-- ── SIDEBAR ─────────────────────────────────────────── -->
    <nav class="sidebar">

        <div class="sidebar-logo">
            <div class="logo-bubble">🫧</div>
            <div class="logo-text">
                <div class="logo-name">BUIH LAUNDRY</div>
                 <div class="logo-sub">Manager</div>
            </div>
        </div>

        <div class="nav-label">General</div>
        <a href="adminDashboard.php" class="nav-link-item ">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="manageAppt.php" class="nav-link-item active">
            <i class="bi bi-calendar-event-fill"></i> Appointments
        </a>
        <a href="manageService.php" class="nav-link-item">
            <i class="bi bi-gear-fill"></i> Services
        </a>

        <div class="nav-label">User Management</div>
        <a href="manageStaff.php" class="nav-link-item">
            <i class="bi bi-person-badge-fill"></i> Staff
        </a>
        <a href="manageCustomer.php" class="nav-link-item">
            <i class="bi bi-people-fill"></i> Customers
        </a>

          <div class="nav-label">Finance</div>
        <a href="manageReceipt.php" class="nav-link-item">
            <i class="bi bi-receipt"></i> Receipts
        </a>


        <div class="sidebar-footer">
            <a href="loginAdmin.php" class="logout-btn">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </div>

    </nav>

    <main class="main-content">
        <header class="dashboard-header">
            <div>
                <h3 class="mb-0">Manage Appointment</h3>
                
            </div>
            <div class="header-right">
                <?php include("notification.php"); ?>
            

                <div class="admin-profile">
                    <div class="avatar">
                        AD
                    </div>
                <div class="admin-info">
                    <div class="admin-name">Administrator</div>
                    <small class="admin-role">Manager</small>    
                </div>
</div>
</div>
</header>

        <div class="table-container">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Appointment List</h4>
               <a href="bookAppt.php" class="btn" style="background:#253154; color:white;">
               <i class="bi bi-plus-lg me-1"></i>New Appointment</a>
            </div>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th> ID</th>
                        <th>Customer Name</th>
                        <th>Phone Number</th>
                        <th>Staff</th>
                        <th> Date</th>
                        <th> Time</th>
                        <th> Remark</th>
                        <th>Collection</th>
                        <th> Status</th>
                        <th>Service</th>
                        <th>Action</th>

                    </tr>
                </thead>
                <tbody>
                   <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>

                        <td><?= htmlspecialchars($row['Appointment_ID'] ?? '-') ?></td>

                        <td><?= htmlspecialchars($row['Customer_Name'] ?? '-') ?></td>

                        <td><?= htmlspecialchars($row['Cust_PhoneNum'] ?? '-') ?></td>

                        <td><?= htmlspecialchars($row['Staff_Name'] ?? '-') ?></td>

                        <td><?= htmlspecialchars($row['Appointment_Date'] ?? '-') ?></td>

                        <td><?= htmlspecialchars($row['Appointment_Time'] ?? '-') ?></td>

                        <td><?= htmlspecialchars($row['Appointment_Remark'] ?? '-') ?></td>

<td>

<?php

if($row['Collection_Method']=="Pickup")
{
?>
<span class="collection-method">
🚗 Self Pickup
</span>
<?php
}
else
{
?>
<span class="collection-method">
🚚 Home Delivery
</span>
<?php
}
?>

</td>

                            <td>
                                <?php
                                $status = strtolower($row['Appointment_Status'] ?? '');

                                $badgeClass = match ($status) {
                                    'pending'   => 'bg-warning text-dark',
                                    'confirmed' => 'bg-primary',
                                    'complete', 'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    default     => 'bg-secondary'
                                };
                                ?>

                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($row['Appointment_Status'] ?? '-') ?>
                                </span>
                                
                        </td>

                        <td>
                            <?= htmlspecialchars($row['Services'] ?? '-') ?>
                        </td>

                        <td>
                            <div class="d-flex gap-2">
                            <a href="editAppt.php?id=<?= $row['Appointment_ID'] ?>" class="btn-action edit-btn"><i class="bi bi-pencil-fill"></i></a> 
                            <a href="deleteAppt.php?id=<?= $row['Appointment_ID'] ?>" class="btn-action delete-btn"
                            onclick="return confirm('Are you sure you want to delete this appointment, this action cannot be undone.');">
                            <i class="bi bi-trash-fill"></i></a>
                   </div>
                        </td>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
        </div>
    </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
</body>
</html>