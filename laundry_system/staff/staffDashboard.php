<?php
session_start();

// ── Auth Guard ───────────────────────────────────────────────────
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: loginStaff.php");
    exit;
}

include("../config/database.php");

$staffId = (int)$_SESSION['staff_id'];

// ── Staff Info ───────────────────────────────────────────────────
$staffQuery = $conn->prepare(
    "SELECT Staff_ID, Staff_Name, Staff_Email, Staff_PhoneNum FROM staff WHERE Staff_ID = ?"
);
$staffQuery->bind_param("i", $staffId);
$staffQuery->execute();
$staffInfo = $staffQuery->get_result()->fetch_assoc();
$staffName     = $staffInfo['Staff_Name']     ?? 'Staff';
$staffEmail    = $staffInfo['Staff_Email']    ?? '';
$staffPhone    = $staffInfo['Staff_PhoneNum'] ?? '';
$staffInitials = implode('', array_map(fn($w) => strtoupper($w[0]), explode(' ', trim($staffName))));

// ── My Appointment Stats ─────────────────────────────────────────
$myTotal = $conn->prepare("SELECT COUNT(*) AS c FROM appointment WHERE Staff_ID = ?");
$myTotal->bind_param("i", $staffId); $myTotal->execute();
$myTotalAppt = $myTotal->get_result()->fetch_assoc()['c'] ?? 0;

$myPending = $conn->prepare("SELECT COUNT(*) AS c FROM appointment WHERE Staff_ID = ? AND Appointment_Status = 'Pending'");
$myPending->bind_param("i", $staffId); $myPending->execute();
$myPendingAppt = $myPending->get_result()->fetch_assoc()['c'] ?? 0;

$myConfirmed = $conn->prepare("SELECT COUNT(*) AS c FROM appointment WHERE Staff_ID = ? AND Appointment_Status = 'Confirmed'");
$myConfirmed->bind_param("i", $staffId); $myConfirmed->execute();
$myConfirmedAppt = $myConfirmed->get_result()->fetch_assoc()['c'] ?? 0;

$myCompleted = $conn->prepare("SELECT COUNT(*) AS c FROM appointment WHERE Staff_ID = ? AND Appointment_Status IN ('Completed','complete')");
$myCompleted->bind_param("i", $staffId); $myCompleted->execute();
$myCompletedAppt = $myCompleted->get_result()->fetch_assoc()['c'] ?? 0;

// ── Today's Appointments ─────────────────────────────────────────
$todayDate   = date('Y-m-d');
$todayQuery  = $conn->prepare("
    SELECT a.Appointment_ID, a.Appointment_Date, a.Appointment_Time,
           a.Appointment_Status, a.Appointment_Remark,
           c.Customer_Name, c.Cust_PhoneNum,
           GROUP_CONCAT(s.Service_Name SEPARATOR ', ') AS Services
    FROM appointment a
    INNER JOIN customer c ON a.Customer_ID = c.Customer_ID
    LEFT  JOIN appointment_service asp ON a.Appointment_ID = asp.Appointment_ID
    LEFT  JOIN service s ON asp.Service_ID = s.Service_ID
    WHERE a.Staff_ID = ? AND a.Appointment_Date = ?
    GROUP BY a.Appointment_ID
    ORDER BY a.Appointment_Time ASC
");
$todayQuery->bind_param("is", $staffId, $todayDate);
$todayQuery->execute();
$todayResult = $todayQuery->get_result();
$todayCount  = $todayResult->num_rows;

// ── Upcoming Appointments ─────────────────────────────────────────
$upcomingQuery = $conn->prepare("
    SELECT a.Appointment_ID, a.Appointment_Date, a.Appointment_Time,
           a.Appointment_Status, c.Customer_Name,
           GROUP_CONCAT(s.Service_Name SEPARATOR ', ') AS Services
    FROM appointment a
    INNER JOIN customer c ON a.Customer_ID = c.Customer_ID
    LEFT  JOIN appointment_service asp ON a.Appointment_ID = asp.Appointment_ID
    LEFT  JOIN service s ON asp.Service_ID = s.Service_ID
    WHERE a.Staff_ID = ? AND a.Appointment_Date > ?
    GROUP BY a.Appointment_ID
    ORDER BY a.Appointment_Date ASC, a.Appointment_Time ASC
    LIMIT 5
");
$upcomingQuery->bind_param("is", $staffId, $todayDate);
$upcomingQuery->execute();
$upcomingResult = $upcomingQuery->get_result();

// ── Notification: pending appointments for bell dropdown ──────────
$notifQuery = $conn->prepare("
    SELECT a.Appointment_ID, a.Appointment_Date, a.Appointment_Time,
           c.Customer_Name,
           GROUP_CONCAT(s.Service_Name SEPARATOR ', ') AS Services
    FROM appointment a
    INNER JOIN customer c ON a.Customer_ID = c.Customer_ID
    LEFT  JOIN appointment_service asp ON a.Appointment_ID = asp.Appointment_ID
    LEFT  JOIN service s ON asp.Service_ID = s.Service_ID
    WHERE a.Staff_ID = ? AND a.Appointment_Status = 'Pending'
    GROUP BY a.Appointment_ID
    ORDER BY a.Appointment_Date ASC, a.Appointment_Time ASC
    LIMIT 10
");
$notifQuery->bind_param("i", $staffId);
$notifQuery->execute();
$notifResult = $notifQuery->get_result();
$notifCount  = $notifResult->num_rows;

// ── helpers ──────────────────────────────────────────────────────
function badgeCls(string $status): string {
    return match(strtolower($status)) {
        'pending'            => 'badge-pending',
        'confirmed'          => 'badge-confirmed',
        'completed','complete' => 'badge-completed',
        'cancelled'          => 'badge-cancelled',
        default              => 'badge-pending',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Staff Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#253154;--brand-lite:#2e3d66;--accent:#2dd4bf;--accent2:#6c8ebf;
    --bg:#f0f2f7;--white:#fff;--text-dark:#1a2340;--text-muted:#6b7a99;
    --radius-lg:16px;--radius-md:12px;
    --shadow-sm:0 2px 8px rgba(37,49,84,.08);--shadow-md:0 6px 24px rgba(37,49,84,.12);
}
body{background:var(--bg);font-family:'Segoe UI',system-ui,sans-serif;color:var(--text-dark);margin:0}
.wrapper{display:flex;min-height:100vh}

/* ── Sidebar ── */
.sidebar{
    width:260px;background:var(--brand);padding:28px 18px;flex-shrink:0;
    display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;
}
.sidebar-logo{
    display:flex;align-items:center;gap:12px;padding:0 8px 24px;
    border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:20px;
}
.logo-bubble{
    width:42px;height:42px;border-radius:12px;
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    display:flex;align-items:center;justify-content:center;font-size:1.3rem;
}
.logo-name{font-weight:700;font-size:1rem;color:#fff}
.logo-sub{font-size:.7rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:1px}

/* Staff profile card in sidebar */
.sidebar-profile{
    background:rgba(45,212,191,.1);border:1px solid rgba(45,212,191,.2);
    border-radius:12px;padding:14px;margin-bottom:20px;
    display:flex;align-items:center;gap:12px;
}
.s-avatar{
    width:44px;height:44px;border-radius:10px;
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    color:#fff;font-weight:700;font-size:.9rem;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.s-name{font-size:.88rem;font-weight:700;color:#fff;line-height:1.2}
.s-role{font-size:.7rem;color:rgba(255,255,255,.5);margin-top:2px}

.nav-label{
    font-size:.68rem;font-weight:600;color:rgba(255,255,255,.35);
    text-transform:uppercase;letter-spacing:1.2px;padding:0 10px;margin:16px 0 8px;
}
.nav-link-item{
    display:flex;align-items:center;gap:12px;padding:10px 14px;
    border-radius:10px;color:rgba(255,255,255,.75);text-decoration:none;
    font-size:.9rem;font-weight:500;transition:background .18s,color .18s;margin-bottom:2px;
}
.nav-link-item:hover,.nav-link-item.active{background:rgba(255,255,255,.12);color:#fff}
.nav-link-item.active{background:rgba(45,212,191,.18);color:var(--accent)}
.nav-link-item i{font-size:1.05rem;width:20px;text-align:center}

.sidebar-footer{
    margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,.1);
}
.logout-btn{
    display:flex;align-items:center;gap:10px;padding:10px 14px;
    border-radius:10px;color:rgba(255,255,255,.6);text-decoration:none;
    font-size:.88rem;transition:background .18s,color .18s;
}
.logout-btn:hover{background:rgba(220,53,69,.2);color:#ff6b6b}

/* ── Main ── */
.main-content{flex:1;padding:32px 36px;overflow-x:hidden}

/* ── Header (matches adminDashboard.php exactly) ── */
.dashboard-header h3{
    margin:0;
    color:#253154;
    font-weight:600;
    font-size:1.75rem;
}
.header-right{
    display:flex;
    align-items:center;
    gap:16px;
}
/* Notification Bell */
.notification-btn{
    position:relative;
    font-size:1.4rem;
    cursor:pointer;
    background:transparent;
    border:none;
    outline:none;
    padding:5px;
    color:#1a2340;
}
.notification-badge{
    position:absolute;
    top:0px;
    right:-2px;
    background:#dc3545;
    color:white;
    border-radius:50%;
    width:18px;
    height:18px;
    font-size:0.68rem;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:600;
}
/* Notification Dropdown */
.notif-wrapper{position:relative;}
.notif-dropdown{
    display:none;
    position:absolute;
    top:calc(100% + 10px);
    right:0;
    width:320px;
    background:#fff;
    border-radius:14px;
    box-shadow:0 12px 40px rgba(37,49,84,.18);
    border:1px solid #eef0f8;
    z-index:9999;
    overflow:hidden;
    animation:fadeSlideDown .18s ease;
}
.notif-dropdown.open{display:block;}
@keyframes fadeSlideDown{
    from{opacity:0;transform:translateY(-6px);}
    to{opacity:1;transform:translateY(0);}
}
.notif-dropdown-header{
    display:flex;align-items:center;justify-content:space-between;
    padding:14px 18px 12px;border-bottom:1px solid #f1f3f8;background:#fafbff;
}
.notif-dropdown-title{font-size:.88rem;font-weight:700;color:#253154;}
.notif-count-pill{
    background:#253154;color:#fff;border-radius:20px;
    padding:2px 10px;font-size:.72rem;font-weight:700;
}
.notif-list{max-height:290px;overflow-y:auto;}
.notif-list::-webkit-scrollbar{width:4px;}
.notif-list::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px;}
.notif-item{
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 18px;border-bottom:1px solid #f4f5fa;transition:background .15s;
}
.notif-item:last-child{border-bottom:none;}
.notif-item:hover{background:#f8f9ff;}
.notif-dot{
    width:8px;height:8px;border-radius:50%;
    background:#f59e0b;margin-top:5px;flex-shrink:0;
}
.notif-body{flex:1;}
.notif-customer{font-size:.85rem;font-weight:700;color:#1a2340;line-height:1.3;}
.notif-service{font-size:.76rem;color:#6b7a99;margin-top:2px;}
.notif-meta{font-size:.72rem;color:#9aa3b8;margin-top:4px;}
.notif-id{font-size:.72rem;font-weight:700;color:#6366f1;white-space:nowrap;padding-top:2px;}
.notif-empty{text-align:center;padding:28px 16px;color:#6b7a99;}
.notif-empty i{font-size:1.8rem;color:#10b981;display:block;margin-bottom:8px;}
.notif-empty p{font-size:.84rem;margin:0;}
.notif-footer{
    padding:10px 18px;background:#fef3c7;
    font-size:.76rem;color:#92400e;font-weight:600;
    text-align:center;border-top:1px solid #fde68a;
}
/* Profile component */
.admin-profile{display:flex;align-items:center;gap:12px;}
.avatar{
    width:45px;height:45px;border-radius:50%;
    background:#1e293b;color:white;font-weight:600;font-size:0.95rem;
    display:flex;align-items:center;justify-content:center;letter-spacing:0.5px;
}
.admin-info{display:flex;flex-direction:column;line-height:1.3;}
.admin-name{font-weight:600;color:#253154;font-size:0.95rem;}
.admin-role{font-size:0.82rem;color:#64748b;}

/* ── Stat Cards ── */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:26px}
.stat-card{
    background:var(--white);border-radius:var(--radius-lg);padding:22px 20px;
    box-shadow:var(--shadow-sm);border-left:4px solid transparent;
    transition:box-shadow .2s,transform .2s;
}
.stat-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px)}
.stat-card.c1{border-left-color:var(--accent)}
.stat-card.c2{border-left-color:#f59e0b}
.stat-card.c3{border-left-color:#6366f1}
.stat-card.c4{border-left-color:#10b981}
.stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.stat-icon{
    width:42px;height:42px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;font-size:1.15rem;
}
.c1 .stat-icon{background:rgba(45,212,191,.12);color:var(--accent)}
.c2 .stat-icon{background:rgba(245,158,11,.12);color:#f59e0b}
.c3 .stat-icon{background:rgba(99,102,241,.12);color:#6366f1}
.c4 .stat-icon{background:rgba(16,185,129,.12);color:#10b981}
.stat-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted)}
.stat-value{font-size:1.9rem;font-weight:800;color:var(--text-dark);line-height:1}
.stat-sub{font-size:.75rem;color:var(--text-muted);margin-top:5px}

/* ── Content Grid ── */
.content-grid{display:grid;grid-template-columns:1fr 310px;gap:22px}

/* ── Panel ── */
.panel{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);overflow:hidden}
.panel-header{
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 22px;border-bottom:1px solid #f1f3f8;
}
.panel-title{font-weight:700;font-size:.98rem;margin:0}
.view-all-btn{
    font-size:.78rem;color:var(--brand);text-decoration:none;font-weight:600;
    padding:5px 12px;border-radius:8px;border:1.5px solid var(--brand);
    transition:background .18s,color .18s;
}
.view-all-btn:hover{background:var(--brand);color:#fff}

/* ── Today Schedule ── */
.schedule-list{padding:14px;display:flex;flex-direction:column;gap:10px}
.schedule-card{
    display:flex;gap:12px;align-items:flex-start;
    background:#fafbff;border:1px solid #eef0f8;border-radius:12px;
    padding:12px 14px;transition:border-color .18s,box-shadow .18s;
}
.schedule-card:hover{border-color:var(--accent);box-shadow:0 2px 10px rgba(45,212,191,.12)}
.time-block{
    min-width:62px;text-align:center;background:var(--brand);color:#fff;
    border-radius:8px;padding:9px 5px;flex-shrink:0;
}
.time-hr{font-size:.88rem;font-weight:700;line-height:1}
.time-pd{font-size:.62rem;opacity:.65;margin-top:2px}
.sched-customer{font-weight:700;font-size:.88rem;color:var(--text-dark)}
.sched-service{font-size:.78rem;color:var(--text-muted);margin-top:3px}
.sched-phone{font-size:.75rem;color:var(--text-muted);margin-top:2px}
.sched-remark{font-size:.75rem;color:#6366f1;margin-top:3px;font-style:italic}

/* badge */
.status-badge{
    display:inline-flex;align-items:center;
    padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:600;white-space:nowrap;
}
.badge-pending  {background:#fef3c7;color:#92400e}
.badge-confirmed{background:#dbeafe;color:#1e40af}
.badge-completed{background:#d1fae5;color:#065f46}
.badge-cancelled{background:#fee2e2;color:#991b1b}

/* update status btn */
.upd-btn{
    padding:4px 11px;border-radius:7px;font-size:.75rem;font-weight:600;
    border:none;cursor:pointer;transition:background .18s,color .18s;
    text-decoration:none;display:inline-block;
    background:var(--brand);color:#fff;
}
.upd-btn:hover{background:var(--brand-lite);color:#fff}

/* ── Upcoming table ── */
.appt-table{width:100%;border-collapse:collapse}
.appt-table thead tr{background:#f8f9fc}
.appt-table th{
    font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;
    color:var(--text-muted);padding:11px 16px;text-align:left;border-bottom:1px solid #eef0f6;
}
.appt-table td{
    padding:12px 16px;font-size:.86rem;color:var(--text-dark);
    border-bottom:1px solid #f4f5fa;vertical-align:middle;
}
.appt-table tbody tr:last-child td{border-bottom:none}
.appt-table tbody tr:hover{background:#fafbff}
.appt-id{font-size:.76rem;color:var(--text-muted);font-weight:600}

/* ── Right side ── */
.side-stack{display:flex;flex-direction:column;gap:20px}

/* Account card */
.acct-header{
    background:linear-gradient(135deg,var(--brand),#3d4f7c);
    padding:28px 22px 20px;text-align:center;
}
.acct-avatar{
    width:64px;height:64px;border-radius:16px;
    background:rgba(255,255,255,.2);color:#fff;font-weight:800;font-size:1.2rem;
    display:flex;align-items:center;justify-content:center;margin:0 auto 12px;
    border:2px solid rgba(255,255,255,.3);
}
.acct-name{font-weight:700;font-size:1rem;color:#fff}
.acct-badge{
    display:inline-flex;align-items:center;gap:5px;
    background:rgba(45,212,191,.25);color:var(--accent);
    border-radius:20px;padding:3px 12px;font-size:.72rem;font-weight:600;
    margin-top:6px;
}
.acct-info-rows{padding:6px 0}
.acct-row{
    display:flex;align-items:center;gap:12px;
    padding:12px 20px;border-bottom:1px solid #f4f5fa;
}
.acct-row:last-child{border-bottom:none}
.acct-icon{
    width:34px;height:34px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;
}
.ai-blue {background:rgba(99,102,241,.1);color:#6366f1}
.ai-teal {background:rgba(45,212,191,.1);color:#0d9488}
.ai-amber{background:rgba(245,158,11,.1);color:#d97706}
.acct-lbl{font-size:.7rem;color:var(--text-muted);line-height:1.2}
.acct-val{font-size:.84rem;font-weight:600;color:var(--text-dark);word-break:break-all}
.edit-profile-btn{
    display:block;margin:14px 18px 18px;text-align:center;
    background:var(--brand);color:#fff;border-radius:10px;
    padding:9px;font-size:.83rem;font-weight:700;text-decoration:none;
    transition:background .18s;
}
.edit-profile-btn:hover{background:var(--brand-lite);color:#fff}

/* date card */
.date-card{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);padding:22px;text-align:center}
.date-num{font-size:2.6rem;font-weight:800;color:var(--brand);line-height:1}
.date-month{font-size:1rem;font-weight:600;color:var(--text-dark);margin-top:4px}
.date-day{font-size:.82rem;color:var(--text-muted);margin-top:2px}
.date-divider{border:none;border-top:1px solid #eef0f8;margin:14px 0}
.date-appt{font-size:.82rem;color:var(--text-muted)}

/* empty state */
.empty-state{text-align:center;padding:30px 16px;color:var(--text-muted)}
.empty-state i{font-size:2.2rem;opacity:.3;display:block;margin-bottom:8px}
.empty-state p{font-size:.86rem;margin:0}

/* modal */
.modal-title-custom{font-weight:700;color:var(--brand)}
.modal-select{
    border:1.5px solid #e2e6f0;border-radius:10px;padding:10px 14px;
    font-size:.9rem;width:100%;color:var(--text-dark);
}
.modal-select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(45,212,191,.15);outline:none}
.btn-save{background:var(--brand);color:#fff;border:none;border-radius:10px;padding:9px 22px;font-weight:700;font-size:.9rem;cursor:pointer;transition:background .18s}
.btn-save:hover{background:var(--brand-lite)}

/* flash */
.flash-success{background:#d1fae5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;padding:10px 16px;font-size:.86rem;margin-bottom:18px;display:flex;align-items:center;gap:8px}
.flash-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;border-radius:10px;padding:10px 16px;font-size:.86rem;margin-bottom:18px;display:flex;align-items:center;gap:8px}
</style>
</head>
<body>
<div class="wrapper">

<!-- ── SIDEBAR ─────────────────────────────────────────── -->
<nav class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-bubble">🫧</div>
        <div>
            <div class="logo-name">BUIH LAUNDRY</div>
            <div class="logo-sub">Staff</div>
        </div>
    </div>

    <div class="sidebar-profile">
        <div class="s-avatar"><?= htmlspecialchars($staffInitials) ?></div>
        <div>
            <div class="s-name"><?= htmlspecialchars($staffName) ?></div>
            <div class="s-role">Staff Member</div>
        </div>
    </div>

    <div class="nav-label">Menu</div>
    <a href="staffDashboard.php" class="nav-link-item active">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
   
    <div class="nav-label">Finance</div>
        <a href="staffListReceipt.php" class="nav-link-item">
            <i class="bi bi-receipt"></i> Receipts
        </a>
   

    <div class="sidebar-footer">
        <a href="logoutStaff.php" class="logout-btn">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</nav>

<!-- ── MAIN ───────────────────────────────────────────── -->
<main class="main-content">

<?php
// ── Handle status update POST ─────────────────────────────────
$flashMsg = '';
$flashType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $apptId    = (int)($_POST['appt_id']    ?? 0);
    $newStatus = trim($_POST['new_status']  ?? '');
    $allowed   = ['Pending','Confirmed','Completed','Cancelled'];

    if ($apptId && in_array($newStatus, $allowed)) {
        // Make sure this appointment actually belongs to this staff
        $check = $conn->prepare("SELECT Appointment_ID FROM appointment WHERE Appointment_ID = ? AND Staff_ID = ?");
        $check->bind_param("ii", $apptId, $staffId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $upd = $conn->prepare("UPDATE appointment SET Appointment_Status = ? WHERE Appointment_ID = ?");
            $upd->bind_param("si", $newStatus, $apptId);
            if ($upd->execute()) {
                $flashMsg  = "Appointment #$apptId status updated to <strong>$newStatus</strong>.";
                $flashType = 'success';
            } else {
                $flashMsg  = "Failed to update. Please try again.";
                $flashType = 'error';
            }
        } else {
            $flashMsg  = "Unauthorised: this appointment is not assigned to you.";
            $flashType = 'error';
        }
    }

    // Re-run today query after update
    $todayQuery->execute();
    $todayResult = $todayQuery->get_result();
    $todayCount  = $todayResult->num_rows;

    // Recount stats
    $myPending->execute();  $myPendingAppt   = $myPending->get_result()->fetch_assoc()['c']   ?? 0;
    $myConfirmed->execute();$myConfirmedAppt  = $myConfirmed->get_result()->fetch_assoc()['c'] ?? 0;
    $myCompleted->execute();$myCompletedAppt  = $myCompleted->get_result()->fetch_assoc()['c'] ?? 0;
    $myTotal->execute();    $myTotalAppt      = $myTotal->get_result()->fetch_assoc()['c']      ?? 0;

    // Refresh notification count after update
    $notifQuery->execute();
    $notifResult = $notifQuery->get_result();
    $notifCount  = $notifResult->num_rows;
}
?>

    <!-- ── HEADER (same structure as adminDashboard.php) ── -->
    <header class="dashboard-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">STAFF DASHBOARD</h3>
            <p>Welcome back, <?= htmlspecialchars($staffName) ?>. Here's your schedule today.</p>
        </div>

        <div class="header-right">

            <!-- Notification Bell with dropdown -->
            <div class="notif-wrapper" id="notifWrapper">
                <button class="notification-btn" id="notifBell" onclick="toggleNotif(event)">
                    <i class="bi bi-bell-fill"></i>
                    <?php if ($notifCount > 0): ?>
                    <span class="notification-badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </button>

                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-dropdown-header">
                        <span class="notif-dropdown-title">
                            <i class="bi bi-bell-fill me-2" style="color:var(--accent)"></i>Pending Tasks
                        </span>
                        <span class="notif-count-pill"><?= $notifCount ?></span>
                    </div>

                    <div class="notif-list">
                    <?php if ($notifCount > 0):
                        $notifResult->data_seek(0);
                        while ($n = $notifResult->fetch_assoc()):
                            $nDate = !empty($n['Appointment_Date']) ? date('d M Y', strtotime($n['Appointment_Date'])) : '-';
                            $nTime = !empty($n['Appointment_Time']) ? date('h:i A', strtotime($n['Appointment_Time'])) : '-';
                    ?>
                        <div class="notif-item">
                            <div class="notif-dot"></div>
                            <div class="notif-body">
                                <div class="notif-customer"><?= htmlspecialchars($n['Customer_Name']) ?></div>
                                <div class="notif-service"><?= htmlspecialchars($n['Services'] ?? '-') ?></div>
                                <div class="notif-meta">
                                    <i class="bi bi-calendar3 me-1"></i><?= $nDate ?>
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-clock me-1"></i><?= $nTime ?>
                                </div>
                            </div>
                            <span class="notif-id">#<?= $n['Appointment_ID'] ?></span>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="notif-empty">
                            <i class="bi bi-check-circle-fill"></i>
                            <p>All caught up! No pending tasks.</p>
                        </div>
                    <?php endif; ?>
                    </div>

                    <?php if ($notifCount > 0): ?>
                    <div class="notif-footer">
                        <i class="bi bi-hourglass-split me-1"></i>
                        <?= $notifCount ?> appointment<?= $notifCount !== 1 ? 's' : '' ?> awaiting action
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- /Notification Bell -->

            <div class="admin-profile">
                <div class="avatar"><?= htmlspecialchars($staffInitials) ?></div>
                <div class="admin-info">
                    <span class="admin-name"><?= htmlspecialchars($staffName) ?></span>
                    <small class="admin-role">Staff Member</small>
                </div>
            </div>

        </div>
    </header>
    <!-- ── END HEADER ── -->

    <?php if ($flashMsg): ?>
    <div class="flash-<?= $flashType ?>">
        <i class="bi bi-<?= $flashType === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' ?>"></i>
        <?= $flashMsg ?>
    </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="stat-grid">
        <div class="stat-card c1">
            <div class="stat-top">
                <div class="stat-label">My Total Jobs</div>
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
            </div>
            <div class="stat-value"><?= $myTotalAppt ?></div>
            <div class="stat-sub">All assigned</div>
        </div>
        <div class="stat-card c2">
            <div class="stat-top">
                <div class="stat-label">Pending</div>
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="stat-value"><?= $myPendingAppt ?></div>
            <div class="stat-sub">Awaiting action</div>
        </div>
        <div class="stat-card c3">
            <div class="stat-top">
                <div class="stat-label">Confirmed</div>
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="stat-value"><?= $myConfirmedAppt ?></div>
            <div class="stat-sub">Ready to service</div>
        </div>
        <div class="stat-card c4">
            <div class="stat-top">
                <div class="stat-label">Completed</div>
                <div class="stat-icon"><i class="bi bi-trophy"></i></div>
            </div>
            <div class="stat-value"><?= $myCompletedAppt ?></div>
            <div class="stat-sub">Successfully done</div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">

        <!-- Left: Today + Upcoming -->
        <div style="display:flex;flex-direction:column;gap:22px;">

            <!-- Today's Schedule -->
            <div class="panel">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="bi bi-sun-fill me-2" style="color:#f59e0b"></i>
                        Today's Schedule
                        <span style="font-size:.78rem;font-weight:500;color:var(--text-muted);margin-left:8px;"><?= date('d M Y') ?></span>
                    </h5>
                    <!--a href="apptList.php" class="view-all-btn">View All</a-->
                </div>

                <div class="schedule-list">
                <?php if ($todayCount > 0):
                    $todayResult->data_seek(0);
                    while ($row = $todayResult->fetch_assoc()):
                        $tp = !empty($row['Appointment_Time'])
                            ? explode(' ', date('h:i A', strtotime($row['Appointment_Time'])))
                            : ['--:--',''];
                ?>
                    <div class="schedule-card">
                        <div class="time-block">
                            <div class="time-hr"><?= $tp[0] ?></div>
                            <div class="time-pd"><?= $tp[1] ?? '' ?></div>
                        </div>
                        <div style="flex:1">
                            <div class="sched-customer"><?= htmlspecialchars($row['Customer_Name']) ?></div>
                            <div class="sched-service"><?= htmlspecialchars($row['Services'] ?? '-') ?></div>
                            <div class="sched-phone"><i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($row['Cust_PhoneNum']) ?></div>
                            <?php if (!empty($row['Appointment_Remark'])): ?>
                            <div class="sched-remark"><i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars($row['Appointment_Remark']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0">
                            <span class="status-badge <?= badgeCls($row['Appointment_Status']) ?>">
                                <?= htmlspecialchars($row['Appointment_Status']) ?>
                            </span>
                            <button class="upd-btn"
                                data-bs-toggle="modal" data-bs-target="#updateModal"
                                data-id="<?= $row['Appointment_ID'] ?>"
                                data-status="<?= htmlspecialchars($row['Appointment_Status']) ?>"
                                data-customer="<?= htmlspecialchars($row['Customer_Name']) ?>">
                                <i class="bi bi-pencil-square me-1"></i>Update
                            </button>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <p>No appointments scheduled for today.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="panel">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="bi bi-calendar-week me-2" style="color:#6366f1"></i>Upcoming Appointments
                    </h5>
                    <!--a href="apptList.php" class="view-all-btn">View All</a-->
                </div>
                <?php if ($upcomingResult->num_rows > 0): ?>
                <table class="appt-table">
                    <thead><tr>
                        <th>#</th><th>Customer</th><th>Service</th>
                        <th>Date</th><th>Time</th><th>Status</th><th>Action</th>
                    </tr></thead>
                    <tbody>
                    <?php while ($row = $upcomingResult->fetch_assoc()):
                        $df = !empty($row['Appointment_Date']) ? date('d M Y', strtotime($row['Appointment_Date'])) : '-';
                        $tf = !empty($row['Appointment_Time']) ? date('h:i A', strtotime($row['Appointment_Time'])) : '-';
                    ?>
                    <tr>
                        <td><span class="appt-id">#<?= htmlspecialchars($row['Appointment_ID']) ?></span></td>
                        <td style="font-weight:600"><?= htmlspecialchars($row['Customer_Name']) ?></td>
                        <td><?= htmlspecialchars($row['Services'] ?? '-') ?></td>
                        <td><?= $df ?></td>
                        <td><?= $tf ?></td>
                        <td><span class="status-badge <?= badgeCls($row['Appointment_Status']) ?>"><?= htmlspecialchars($row['Appointment_Status']) ?></span></td>
                        <td>
                            <button class="upd-btn"
                                data-bs-toggle="modal" data-bs-target="#updateModal"
                                data-id="<?= $row['Appointment_ID'] ?>"
                                data-status="<?= htmlspecialchars($row['Appointment_Status']) ?>"
                                data-customer="<?= htmlspecialchars($row['Customer_Name']) ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state" style="padding:22px">
                        <p>No upcoming appointments.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /left col -->

        <!-- Right: Account + Date -->
        <div class="side-stack">

            <!-- My Account Card -->
            <div class="panel">
                <div class="acct-header">
                    <div class="acct-avatar"><?= htmlspecialchars($staffInitials) ?></div>
                    <div class="acct-name"><?= htmlspecialchars($staffName) ?></div>
                    <div class="acct-badge"><i class="bi bi-person-badge-fill"></i> Staff Member</div>
                </div>
                <div class="acct-info-rows">
                    <div class="acct-row">
                        <div class="acct-icon ai-blue"><i class="bi bi-person-fill"></i></div>
                        <div>
                            <div class="acct-lbl">Staff ID</div>
                            <div class="acct-val">#<?= htmlspecialchars($staffInfo['Staff_ID']) ?></div>
                        </div>
                    </div>
                    <div class="acct-row">
                        <div class="acct-icon ai-teal"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <div class="acct-lbl">Email</div>
                            <div class="acct-val"><?= htmlspecialchars($staffEmail) ?></div>
                        </div>
                    </div>
                    <div class="acct-row">
                        <div class="acct-icon ai-amber"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <div class="acct-lbl">Phone</div>
                            <div class="acct-val"><?= htmlspecialchars($staffPhone ?: 'N/A') ?></div>
                        </div>
                    </div>
                </div>
                <a href="#" class="edit-profile-btn" data-bs-toggle="modal" data-bs-target="#profileModal">
                    <i class="bi bi-pencil-fill me-1"></i> View My Account
                </a>
            </div>

            <!-- Date Card -->
            <div class="date-card">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:6px">Today</div>
                <div class="date-num"><?= date('d') ?></div>
                <div class="date-month"><?= date('F Y') ?></div>
                <div class="date-day"><?= date('l') ?></div>
                <hr class="date-divider">
                <div class="date-appt">
                    <i class="bi bi-calendar-event me-1"></i>
                    <?= $todayCount ?> appointment<?= $todayCount !== 1 ? 's' : '' ?> today
                </div>
            </div>

        </div><!-- /right -->

    </div><!-- /content-grid -->

</main>
</div><!-- /wrapper -->

<!-- ── UPDATE STATUS MODAL ─────────────────────────────────────── -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header" style="border-bottom:1px solid #f1f3f8;padding:20px 24px">
                <h5 class="modal-title modal-title-custom">
                    <i class="bi bi-pencil-square me-2" style="color:var(--accent)"></i>Update Appointment Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="staffDashboard.php">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="appt_id" id="modal_appt_id">
                <div class="modal-body" style="padding:24px">
                    <p style="font-size:.86rem;color:var(--text-muted);margin-bottom:16px">
                        Updating status for appointment <strong id="modal_customer_name"></strong>
                        <span style="font-size:.8rem;color:#6366f1">(#<span id="modal_appt_id_display"></span>)</span>
                    </p>
                    <label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--brand);margin-bottom:6px;display:block">New Status</label>
                    <select name="new_status" id="modal_status" class="modal-select">
                        <option value="Pending">⏳ Pending</option>
                        <option value="Confirmed">✅ Confirmed</option>
                        <option value="Completed">🏆 Completed</option>
                        <option value="Cancelled">❌ Cancelled</option>
                    </select>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f1f3f8;padding:16px 24px;gap:10px">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:9px">Cancel</button>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── MY PROFILE MODAL ────────────────────────────────────────── -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header" style="border-bottom:1px solid #f1f3f8;padding:20px 24px">
                <h5 class="modal-title modal-title-custom">
                    <i class="bi bi-person-circle me-2" style="color:var(--accent)"></i>My Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <div style="text-align:center;margin-bottom:24px">
                    <div style="width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,var(--brand),var(--accent2));color:#fff;font-weight:800;font-size:1.3rem;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <?= htmlspecialchars($staffInitials) ?>
                    </div>
                    <div style="font-weight:700;font-size:1.05rem;color:var(--brand)"><?= htmlspecialchars($staffName) ?></div>
                    <div style="font-size:.78rem;color:var(--text-muted);margin-top:3px">Staff Member · ID #<?= $staffId ?></div>
                </div>
                <table style="width:100%;border-collapse:collapse">
                    <?php foreach ([
                        ['bi-person-fill','Name', $staffName],
                        ['bi-envelope-fill','Email', $staffEmail],
                        ['bi-telephone-fill','Phone', $staffPhone ?: 'N/A'],
                    ] as [$icon,$label,$value]): ?>
                    <tr style="border-bottom:1px solid #f4f5fa">
                        <td style="padding:12px 8px;width:36px">
                            <span style="width:30px;height:30px;border-radius:8px;background:rgba(37,49,84,.08);display:flex;align-items:center;justify-content:center">
                                <i class="bi <?= $icon ?>" style="font-size:.85rem;color:var(--brand)"></i>
                            </span>
                        </td>
                        <td style="padding:12px 8px;font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:80px"><?= $label ?></td>
                        <td style="padding:12px 8px;font-size:.88rem;font-weight:600;color:var(--text-dark)"><?= htmlspecialchars($value) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <div style="margin-top:18px;background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px;font-size:.78rem;color:#1e40af">
                    <i class="bi bi-info-circle me-1"></i>
                    To update your account details, please contact your manager or administrator.
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f3f8;padding:14px 24px">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:9px">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Populate Update Status modal
document.getElementById('updateModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modal_appt_id').value               = btn.dataset.id;
    document.getElementById('modal_appt_id_display').textContent = btn.dataset.id;
    document.getElementById('modal_customer_name').textContent   = btn.dataset.customer;
    document.getElementById('modal_status').value                = btn.dataset.status;
});

// Notification bell toggle
function toggleNotif(e) {
    e.stopPropagation();
    document.getElementById('notifDropdown').classList.toggle('open');
}
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('notifWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('notifDropdown').classList.remove('open');
    }
});
</script>
</body>
</html>