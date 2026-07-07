<?php
/*
 * staffNotification.php
 * ─────────────────────────────────────────────────────────────────
 * Included inside staffDashboard.php (session + $conn + $staffId
 * are already available from the parent file).
 *
 * Shows a bell icon with a badge count of pending appointments
 * assigned to the logged-in staff, plus a dropdown list of those
 * appointments with customer name, service, date and time.
 */

// ── Fetch pending appointments for this staff ─────────────────────
$notifQuery = $conn->prepare("
    SELECT
        a.Appointment_ID,
        a.Appointment_Date,
        a.Appointment_Time,
        c.Customer_Name,
        GROUP_CONCAT(s.Service_Name SEPARATOR ', ') AS Services
    FROM appointment a
    INNER JOIN customer c ON a.Customer_ID = c.Customer_ID
    LEFT  JOIN appointment_service asp ON a.Appointment_ID = asp.Appointment_ID
    LEFT  JOIN service s ON asp.Service_ID = s.Service_ID
    WHERE a.Staff_ID = ?
      AND a.Appointment_Status = 'Pending'
    GROUP BY a.Appointment_ID
    ORDER BY a.Appointment_Date ASC, a.Appointment_Time ASC
    LIMIT 10
");
$notifQuery->bind_param("i", $staffId);
$notifQuery->execute();
$notifResult = $notifQuery->get_result();
$notifCount  = $notifResult->num_rows;
?>

<!-- ── NOTIFICATION BELL ──────────────────────────────────────── -->
<div class="notif-wrapper" id="notifWrapper">

    <button class="notification-btn" id="notifBell" onclick="toggleNotif(event)" title="Pending appointments">
        <i class="bi bi-bell-fill"></i>
        <?php if ($notifCount > 0): ?>
        <span class="notification-badge"><?= $notifCount ?></span>
        <?php endif; ?>
    </button>

    <!-- Dropdown -->
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
                $nDate = !empty($n['Appointment_Date'])
                    ? date('d M Y', strtotime($n['Appointment_Date'])) : '-';
                $nTime = !empty($n['Appointment_Time'])
                    ? date('h:i A', strtotime($n['Appointment_Time'])) : '-';
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

    </div><!-- /notif-dropdown -->
</div><!-- /notif-wrapper -->

<style>
/* ── Notification wrapper & bell ─────────────────────────────── */
.notif-wrapper {
    position: relative;
}

.notification-btn {
    position: relative;
    font-size: 1.25rem;
    cursor: pointer;
    background: #fff;
    border: none;
    outline: none;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1a2340;
    box-shadow: 0 2px 8px rgba(37,49,84,.08);
    transition: box-shadow .18s, background .18s;
}
.notification-btn:hover { box-shadow: 0 6px 18px rgba(37,49,84,.14); background: #f8f9ff; }

.notification-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: #fff;
    border-radius: 50%;
    width: 19px;
    height: 19px;
    font-size: .65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    border: 2px solid #f0f2f7;
    animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
    0%,100% { transform: scale(1); }
    50%      { transform: scale(1.15); }
}

/* ── Dropdown panel ──────────────────────────────────────────── */
.notif-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 320px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(37,49,84,.18);
    border: 1px solid #eef0f8;
    z-index: 9999;
    overflow: hidden;
    animation: fadeSlideDown .18s ease;
}
.notif-dropdown.open { display: block; }

@keyframes fadeSlideDown {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.notif-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 18px 12px;
    border-bottom: 1px solid #f1f3f8;
    background: #fafbff;
}
.notif-dropdown-title {
    font-size: .88rem;
    font-weight: 700;
    color: #253154;
}
.notif-count-pill {
    background: #253154;
    color: #fff;
    border-radius: 20px;
    padding: 2px 10px;
    font-size: .72rem;
    font-weight: 700;
}

/* ── Notification list ───────────────────────────────────────── */
.notif-list {
    max-height: 300px;
    overflow-y: auto;
}
.notif-list::-webkit-scrollbar { width: 4px; }
.notif-list::-webkit-scrollbar-track { background: #f8f9fc; }
.notif-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px 18px;
    border-bottom: 1px solid #f4f5fa;
    transition: background .15s;
    cursor: default;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f8f9ff; }

.notif-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f59e0b;
    margin-top: 5px;
    flex-shrink: 0;
}

.notif-body { flex: 1; }
.notif-customer { font-size: .85rem; font-weight: 700; color: #1a2340; line-height: 1.3; }
.notif-service  { font-size: .76rem; color: #6b7a99; margin-top: 2px; }
.notif-meta     { font-size: .72rem; color: #9aa3b8; margin-top: 4px; }

.notif-id {
    font-size: .72rem;
    font-weight: 700;
    color: #6366f1;
    white-space: nowrap;
    padding-top: 2px;
}

.notif-empty {
    text-align: center;
    padding: 28px 16px;
    color: #6b7a99;
}
.notif-empty i  { font-size: 1.8rem; color: #10b981; display: block; margin-bottom: 8px; }
.notif-empty p  { font-size: .84rem; margin: 0; }

.notif-footer {
    padding: 10px 18px;
    background: #fef3c7;
    font-size: .76rem;
    color: #92400e;
    font-weight: 600;
    text-align: center;
    border-top: 1px solid #fde68a;
}
</style>

<script>
function toggleNotif(e) {
    e.stopPropagation();
    document.getElementById('notifDropdown').classList.toggle('open');
}
// Close when clicking outside
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('notifWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        const dd = document.getElementById('notifDropdown');
        if (dd) dd.classList.remove('open');
    }
});
</script>