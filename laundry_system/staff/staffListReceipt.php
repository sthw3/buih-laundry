<?php
session_start();

// ── Auth Guard ───────────────────────────────────────────────────
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: loginStaff.php");
    exit;
}

include("../config/database.php");

$staffId = (int)$_SESSION['staff_id'];

// ── Staff Info (for sidebar / topbar) ─────────────────────────────
$staffQuery = $conn->prepare(
    "SELECT Staff_ID, Staff_Name, Staff_Email, Staff_PhoneNum FROM staff WHERE Staff_ID = ?"
);
$staffQuery->bind_param("i", $staffId);
$staffQuery->execute();
$staffInfo = $staffQuery->get_result()->fetch_assoc();
$staffName     = $staffInfo['Staff_Name'] ?? 'Staff';
$staffInitials = implode('', array_map(fn($w) => strtoupper($w[0]), explode(' ', trim($staffName))));

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

// ── Receipts only for appointments assigned to THIS staff ─────────
$stmt = $conn->prepare(
   "SELECT 
        receipt.receipt_ID AS Receipt_ID,
        receipt.Appointment_ID,
        receipt.Payment_ID,
        receipt.Total_amount,
        receipt.Issued_Date,

        customer.Customer_Name,
        customer.Cust_PhoneNum,

        payment.Payment_Method,

        GROUP_CONCAT(service.Service_Name SEPARATOR ', ') AS Services

    FROM receipt

    INNER JOIN appointment 
        ON receipt.Appointment_ID = appointment.Appointment_ID

    INNER JOIN customer 
        ON appointment.Customer_ID = customer.Customer_ID

    LEFT JOIN appointment_service 
        ON appointment.Appointment_ID = appointment_service.Appointment_ID

    LEFT JOIN service 
        ON appointment_service.Service_ID = service.Service_ID

    LEFT JOIN payment
        ON receipt.Payment_ID = payment.Payment_ID

    WHERE appointment.Staff_ID = ?

    GROUP BY receipt.receipt_ID
    ORDER BY receipt.Issued_Date DESC"
);
$stmt->bind_param("i", $staffId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Receipts – Buih Laundry</title>
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

/* ── Sidebar (same as staffDashboard.php) ── */
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

.dashboard-header{
    display:flex;justify-content:space-between;align-items:center;
    margin-bottom:25px;padding:20px 25px;background:#fff;
    border-radius:15px;box-shadow:0 4px 12px rgba(0,0,0,.08);
}
.header-right{display:flex;align-items:center;gap:20px}
.admin-profile{display:flex;align-items:center;gap:12px}
.avatar{
    width:45px;height:45px;border-radius:50%;
    background:var(--brand);color:#fff;font-weight:600;
    display:flex;align-items:center;justify-content:center;
}
.admin-info{display:flex;flex-direction:column;line-height:1.2}
.admin-name{font-weight:600;color:var(--brand)}
.admin-role{font-size:.85rem;color:#6c757d}

/* ── Notification Bell (same as staffDashboard.php) ── */
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

/* ── Table (same as manageReceipt.php) ── */
.table-container{
    background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.08);
    overflow:hidden;border:1px solid #f0f0f0;
}
.card-header{padding:20px 25px;border-bottom:1px solid #eee;background:#fff}
.table{margin-bottom:0}
.table thead{background-color:#f8f9fa;text-transform:uppercase;font-size:.8rem;letter-spacing:1px;color:#6c757d}
.table td,.table th{padding:1.25rem;vertical-align:middle}
.btn-action{padding:5px 12px;border-radius:6px;text-decoration:none;font-size:.85rem;margin-right:5px}

.empty-state{text-align:center;padding:40px 16px;color:var(--text-muted)}
.empty-state i{font-size:2.2rem;opacity:.3;display:block;margin-bottom:8px}
.empty-state p{font-size:.9rem;margin:0}
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
    <a href="staffDashboard.php" class="nav-link-item">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>

    <div class="nav-label">Finance</div>
    <a href="staffListReceipt.php" class="nav-link-item active">
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
    <header class="dashboard-header">
        <div>
            <h3 class="mb-0">Customer List Receipts</h3>
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
                    <div class="admin-name"><?= htmlspecialchars($staffName) ?></div>
                    <small class="admin-role">Staff Member</small>
                </div>
            </div>
        </div>
    </header>

    <div class="table-container">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Receipt List</h4>
        </div>

        <?php if ($result->num_rows > 0): ?>
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Appointment ID</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Services</th>
                    <th>Total Amount(RM)</th>
                    <th>Issued Date</th>
                    <th>Payment Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
               <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Receipt_ID'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Appointment_ID'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Customer_Name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Cust_PhoneNum'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Services'] ?? '-') ?></td>
                    <td>RM <?= htmlspecialchars(number_format($row['Total_amount'] ?? 0, 2)) ?></td>
                    <td><?= htmlspecialchars($row['Issued_Date'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Payment_Method'] ?? '-') ?></td>
                    <td>
                        <a href="staffViewReceipt.php?id=<?= $row['Receipt_ID'] ?>" class="btn-action" style="background:#e8f5e9;color:#2e7d32;" target="_blank">
                            <i class="bi bi-printer-fill"></i> </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-receipt-cutoff"></i>
                <p>No receipts found.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
</div><!-- /wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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