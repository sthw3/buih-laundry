<?php
include("../config/database.php");

// ── Stats Queries ────────────────────────────────────────────────
$totalAppt    = $conn->query("SELECT COUNT(*) AS total FROM appointment")->fetch_assoc()['total'] ?? 0;
$pendingAppt  = $conn->query("SELECT COUNT(*) AS total FROM appointment WHERE Appointment_Status = 'Pending'")->fetch_assoc()['total'] ?? 0;
$confirmedAppt= $conn->query("SELECT COUNT(*) AS total FROM appointment WHERE Appointment_Status = 'Confirmed'")->fetch_assoc()['total'] ?? 0;
$totalStaff   = $conn->query("SELECT COUNT(*) AS total FROM staff")->fetch_assoc()['total'] ?? 0;
$totalCustomer= $conn->query("SELECT COUNT(*) AS total FROM customer")->fetch_assoc()['total'] ?? 0;

// Total revenue from receipts (sum of service prices via appointment_service → service)
$revenueResult = $conn->query("
    SELECT COALESCE(SUM(s.Price), 0) AS total_revenue
    FROM appointment_service aps
    INNER JOIN service s ON aps.Service_ID = s.Service_ID
    INNER JOIN appointment a ON aps.Appointment_ID = a.Appointment_ID
    WHERE a.Appointment_Status IN ('Completed','completed','complete')
");
$totalRevenue = $revenueResult ? $revenueResult->fetch_assoc()['total_revenue'] : 0;

// Total receipts count (completed appointments)
$totalReceipts = $conn->query("SELECT COUNT(*) AS total FROM appointment WHERE Appointment_Status IN ('Completed','completed','complete')")->fetch_assoc()['total'] ?? 0;

// ── Recent Appointments (latest 5) ───────────────────────────────
$recentQuery = "
    SELECT
        appointment.Appointment_ID,
        appointment.Appointment_Date,
        appointment.Appointment_Time,
        appointment.Appointment_Status,
        customer.Customer_Name,
        staff.Staff_Name,
        GROUP_CONCAT(service.Service_Name SEPARATOR ', ') AS Services
    FROM appointment
    INNER JOIN customer ON appointment.Customer_ID = customer.Customer_ID
    INNER JOIN staff    ON appointment.Staff_ID    = staff.Staff_ID
    LEFT  JOIN appointment_service ON appointment.Appointment_ID = appointment_service.Appointment_ID
    LEFT  JOIN service ON appointment_service.Service_ID = service.Service_ID
    GROUP BY appointment.Appointment_ID
    ORDER BY appointment.Appointment_ID DESC
    LIMIT 5
";
$recentResult = $conn->query($recentQuery);

// ── Appointment Status Breakdown ─────────────────────────────────
$statusBreakdown = [];
$statusQuery = $conn->query("SELECT Appointment_Status, COUNT(*) AS cnt FROM appointment GROUP BY Appointment_Status");
if ($statusQuery) {
    while ($row = $statusQuery->fetch_assoc()) {
        $statusBreakdown[strtolower($row['Appointment_Status'])] = $row['cnt'];
    }
}

// ── Pending notifications count ──────────────────────────────────
$pendingNotif = $pendingAppt;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ───────────────────────── Base ──────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

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

body {
    background: var(--bg);
    font-family: 'Segoe UI', system-ui, sans-serif;
    color: var(--text-dark);
    margin: 0;
}

/* ───────────────────────── Layout ───────────────────────────── */
.wrapper { display: flex; min-height: 100vh; }

/* ───────────────────────── Sidebar ───────────────────────────────────── */
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
.logout-btn:hover { background: rgba(220,53,69,.2); color: #ff6b6b; }

/* ─── Main Content ─────────────────────────────────────────────── */
.main-content { flex: 1; padding: 32px 36px; overflow-x: hidden; }

.dashboard-header h3{
    margin:0;
    color:#253154;
    font-weight:600;
}

/* ─── Header & Right Navigation ───────────────────────── */
.dashboard-header h3 {
    margin: 0;
    color: #253154;
    font-weight: 600;
    font-size: 1.75rem; 
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

/* Notification Badge Container */
.notification-btn {
    position: relative;
    font-size: 1.4rem;
    cursor: pointer;
    background: transparent;
    border: none;
    outline: none;
    padding: 5px;    
    color: #1a2340;
}

.notification-badge {
    position: absolute;
    top: 0px;
    right: -2px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 0.68rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

/* ───────────────────────── Profile Component ────────────────── */
.admin-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #1e293b; /* Deep slate / Navy background */
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.5px;
}

.admin-info {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}

.admin-name {
    font-weight: 600;
    color: #253154; 
    font-size: 0.95rem;
}

.admin-role {
    font-size: 0.82rem;
    color: #64748b;
}

/* ─── Stat Cards ───────────────────────────────────────────────── */
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px; }

.stat-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    border-top: 3px solid transparent;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: box-shadow .2s, transform .2s;
}
.stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

.stat-card.c1 { border-top-color: var(--accent); }
.stat-card.c2 { border-top-color: #6366f1; }
.stat-card.c3 { border-top-color: #f59e0b; }
.stat-card.c4 { border-top-color: #10b981; }

.stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.c1 .stat-icon { background: rgba(45,212,191,.12); color: var(--accent); }
.c2 .stat-icon { background: rgba(99,102,241,.12); color: #6366f1; }
.c3 .stat-icon { background: rgba(245,158,11,.12); color: #f59e0b; }
.c4 .stat-icon { background: rgba(16,185,129,.12); color: #10b981; }

.stat-body { flex: 1; }
.stat-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: var(--text-muted); margin-bottom: 6px; }
.stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-dark); line-height: 1; }
.stat-sub   { font-size: .78rem; color: var(--text-muted); margin-top: 6px; }

/* ─── Content Grid ─────────────────────────────────────────────── */
.content-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; }

/* ─── Panel / Card ─────────────────────────────────────────────── */
.panel {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f3f8;
}

.panel-title { font-weight: 700; font-size: 1rem; margin: 0; }

.view-all-btn {
    font-size: .8rem;
    color: var(--brand);
    text-decoration: none;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 8px;
    border: 1.5px solid var(--brand);
    transition: background .18s, color .18s;
}
.view-all-btn:hover { background: var(--brand); color: #fff; }

/* ─── Table ────────────────────────────────────────────────────── */
.appt-table { width: 100%; border-collapse: collapse; }
.appt-table thead tr { background: #f8f9fc; }
.appt-table th {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--text-muted);
    padding: 12px 18px;
    text-align: left;
    border-bottom: 1px solid #eef0f6;
}
.appt-table td {
    padding: 14px 18px;
    font-size: .875rem;
    color: var(--text-dark);
    border-bottom: 1px solid #f4f5fa;
    vertical-align: middle;
}
.appt-table tbody tr:last-child td { border-bottom: none; }
.appt-table tbody tr:hover { background: #fafbff; }

.cust-name { font-weight: 600; }
.appt-id   { font-size: .78rem; color: var(--text-muted); font-weight: 600; }

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: .74rem;
    font-weight: 600;
}
.badge-pending   { background: #fef3c7; color: #92400e; }
.badge-confirmed { background: #dbeafe; color: #1e40af; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }



/* ─── Side Panel ───────────────────────────────────────────────── */
.side-stack { display: flex; flex-direction: column; gap: 20px; }

.summary-list { padding: 8px 0; }
.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    border-bottom: 1px solid #f4f5fa;
}
.summary-row:last-child { border-bottom: none; }

.summary-label { display: flex; align-items: center; gap: 10px; font-size: .875rem; font-weight: 500; }
.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.dot-pending   { background: #f59e0b; }
.dot-confirmed { background: #6366f1; }
.dot-completed { background: #10b981; }
.dot-cancelled { background: #ef4444; }

.summary-count { font-weight: 700; font-size: .95rem; }

/* quick actions */
.quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 20px; }
.quick-btn {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 8px;
    padding: 16px 8px;
    border-radius: 12px;
    border: 1.5px solid #e8eaf2;
    text-decoration: none;
    color: var(--text-dark);
    font-size: .8rem;
    font-weight: 600;
    transition: background .18s, border-color .18s, color .18s;
}
.quick-btn i { font-size: 1.3rem; }
.quick-btn:hover { background: var(--brand); color: #fff; border-color: var(--brand); }

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
        <a href="adminDashboard.php" class="nav-link-item active">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="manageAppt.php" class="nav-link-item">
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

    <!-- ── MAIN ───────────────────────────────────────────── -->
   
        <main class="main-content">
        <header class="dashboard-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">ADMIN DASHBOARD</h3>
         <p>Welcome back, Manager. Here's what's happening today.</p>
    </div>
    
    <div class="header-right">

<?php include("notification.php"); ?>

<div class="admin-profile">



        <div class="admin-profile">
            <div class="avatar">AD</div>
            <div class="admin-info">
                <span class="admin-name">Admin</span>
                <small class="admin-role">Manager</small>    
            </div>
        </div>
    </div>
</header>



        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card c1">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-body">
                    <div class="stat-label">Total Appointments</div>
                    <div class="stat-value"><?= $totalAppt ?></div>
                    <div class="stat-sub"><?= $pendingAppt ?> pending · <?= $confirmedAppt ?> confirmed</div>
                </div>
            </div>
            <div class="stat-card c2">
                <div class="stat-icon"><i class="bi bi-person-badge-fill"></i></div>
                <div class="stat-body">
                    <div class="stat-label">Staff Members</div>
                    <div class="stat-value"><?= $totalStaff ?></div>
                    <div class="stat-sub">Active team members</div>
                </div>
            </div>
            <div class="stat-card c3">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-body">
                    <div class="stat-label">Customers</div>
                    <div class="stat-value"><?= $totalCustomer ?></div>
                    <div class="stat-sub">Registered customers</div>
                </div>
            </div>
            <div class="stat-card c4">
                <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-body">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">RM <?= number_format($totalRevenue, 0) ?></div>
                    <div class="stat-sub">From <?= $totalReceipts ?> completed jobs</div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">

            <!-- Recent Appointments Table -->
            <div class="panel">
                <div class="panel-header">
                    <h5 class="panel-title">Recent Appointments</h5>
                    <a href="manageAppt.php" class="view-all-btn">View All</a>
                </div>
                <table class="appt-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Staff</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($recentResult && $recentResult->num_rows > 0): ?>
                    <?php while ($row = $recentResult->fetch_assoc()): ?>
                        <?php
                            $status = strtolower($row['Appointment_Status'] ?? '');
                            $badgeCls = match($status) {
                                'pending'   => 'badge-pending',
                                'confirmed' => 'badge-confirmed',
                                'completed','complete' => 'badge-completed',
                                'cancelled' => 'badge-cancelled',
                                default     => 'badge-pending',
                            };
                            $dateFormatted = !empty($row['Appointment_Date'])
                                ? date('d M Y', strtotime($row['Appointment_Date'])) : '-';
                            $timeFormatted = !empty($row['Appointment_Time'])
                                ? date('h:i A', strtotime($row['Appointment_Time'])) : '-';
                        ?>
                        <tr>
                            <td><span class="appt-id">#<?= htmlspecialchars($row['Appointment_ID']) ?></span></td>
                            <td><span class="cust-name"><?= htmlspecialchars($row['Customer_Name']) ?></span></td>
                            <td><?= htmlspecialchars($row['Staff_Name']) ?></td>
                            <td><?= htmlspecialchars($row['Services'] ?? '-') ?></td>
                            <td><?= $dateFormatted ?></td>
                            <td><?= $timeFormatted ?></td>
                            <td>
                                <span class="status-badge <?= $badgeCls ?>">
                                    <?= htmlspecialchars($row['Appointment_Status']) ?>
                                </span>
                            </td>
                            
                        </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No appointments found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Right Side Stack -->
            <div class="side-stack">

                <!-- Appointment Summary -->
                <div class="panel">
                    <div class="panel-header">
                        <h5 class="panel-title">Appointment Status</h5>
                    </div>
                    <div class="summary-list">
                        <div class="summary-row">
                            <span class="summary-label"><span class="dot dot-pending"></span>Pending</span>
                            <span class="summary-count"><?= $statusBreakdown['pending'] ?? 0 ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label"><span class="dot dot-confirmed"></span>Confirmed</span>
                            <span class="summary-count"><?= $statusBreakdown['confirmed'] ?? 0 ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label"><span class="dot dot-completed"></span>Completed</span>
                            <span class="summary-count"><?= ($statusBreakdown['completed'] ?? 0) + ($statusBreakdown['complete'] ?? 0) ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label"><span class="dot dot-cancelled"></span>Cancelled</span>
                            <span class="summary-count"><?= $statusBreakdown['cancelled'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="panel">
                    <div class="panel-header">
                        <h5 class="panel-title">Quick Actions</h5>
                    </div>
                    <div class="quick-actions">
                        <a href="bookAppt.php" class="quick-btn">
                            <i class="bi bi-calendar-plus-fill"></i>
                            New Appointment
                        </a>
                        <a href="createStaff.php" class="quick-btn">
                            <i class="bi bi-person-plus-fill"></i>
                            Add Staff
                        </a>
                        <a href="createCustomer.php" class="quick-btn">
                            <i class="bi bi-person-check-fill"></i>
                            Add Customer
                        </a>
                        <a href="createService.php" class="quick-btn">
                            <i class="bi bi-plus-circle-fill"></i>
                            Add Service
                        </a>
                    </div>
                </div>

            </div><!-- /side-stack -->

        </div><!-- /content-grid -->

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>