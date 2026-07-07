<?php

$notif = mysqli_query($conn, "

SELECT
Appointment_ID,
Appointment_Status,
Appointment_Date,
Appointment_Time

FROM appointment

ORDER BY Appointment_ID DESC

LIMIT 6

");

?>

<div class="dropdown">

<button
class="notification-btn"
type="button"
data-bs-toggle="dropdown">

<i class="bi bi-bell-fill"></i>

<?php

$total=mysqli_num_rows($notif);

if($total>0)
{

?>

<span class="notification-badge">

<?= $total ?>

</span>

<?php

}

?>

</button>

<ul class="dropdown-menu dropdown-menu-end shadow"
style="width:340px;">

<li>

<h6 class="dropdown-header">

Notifications

</h6>

</li>

<?php

mysqli_data_seek($notif,0);

while($row=mysqli_fetch_assoc($notif))
{

$status=strtolower($row['Appointment_Status']);

if($status=="pending")
{
    $icon="🟡";
}

elseif($status=="confirmed")
{
    $icon="🔵";
}

elseif($status=="completed")
{
    $icon="🟢";
}

elseif($status=="cancelled")
{
    $icon="🔴";
}

else
{
    $icon="⚪";
}

?>

<li>

<a
class="dropdown-item"
href="editAppt.php?id=<?= $row['Appointment_ID']; ?>">

<strong>

<?= $icon ?>

Appointment #<?= $row['Appointment_ID']; ?>

</strong>

<br>

<small>

<?= ucfirst($status); ?>

<br>

<?= date("d M Y",strtotime($row['Appointment_Date'])); ?>

<?= date("g:i A",strtotime($row['Appointment_Time'])); ?>

</small>

</a>

</li>

<?php

}

?>

</ul>

</div>