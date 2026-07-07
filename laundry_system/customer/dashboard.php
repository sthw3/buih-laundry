<?php
session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

$customer_name = $_SESSION['customer_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Customer Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:900px;
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

.welcome{
    text-align:center;
    margin-bottom:30px;
}

.welcome h2{
    color:#253154;
    font-weight:bold;
}

.menu{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:20px;
}

.menu a{
    text-decoration:none;
}

.menu-card{
    background:#fff;
    border:none;
    border-radius:18px;
    padding:30px;
    text-align:center;
    transition:.3s ease;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    height:180px;

    display:flex;
    flex-direction:column;
    justify-content:center;
}

.menu-card:hover{

    transform:translateY(-8px);

    box-shadow:0 15px 30px rgba(37,49,84,.18);

}

.menu-card i{

    font-size:48px;

    color:#253154;

    margin-bottom:15px;

}

.menu-card h5{

    font-weight:600;

    color:#253154;

    margin-bottom:8px;

}

.logout{
    margin-top:30px;
    text-align:center;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<h3>
Customer Dashboard
</h3>

</div>

<div class="card-body">

<div class="welcome">

<div class="text-center mb-4">

<i class="bi bi-person-circle"
style="font-size:70px;color:#253154;"></i>

<h2 class="mt-3">

Welcome,

<span style="color:#253154;">

<?php echo $customer_name; ?>

</span>

👋

</h2>

<p class="text-muted">

Manage your laundry appointments quickly and easily.

</p>

</div>
<p class="text-muted">
Manage your laundry booking here.
</p>

</div>

<div class="menu">

<a href="view_account.php">

<div class="menu-card">

<i class="bi bi-person-circle"></i>

<h5>My Profile</h5>

<p class="text-muted small mb-0">

View your personal information.

</p>

</div>

</a>


<a href="booking.php">

<div class="menu-card">

<i class="bi bi-calendar-plus"></i>

<h5>Book Appointment</h5>

<p class="text-muted small mb-0">

Schedule a new laundry service.

</p>

</div>


</a>


<a href="view_appointment.php">

<div class="menu-card">

<i class="bi bi-card-list"></i>

<h5>View Appointment</h5>

<p class="text-muted small mb-0">

Track, cancel or view receipts.

</p>

</div>

</a>


</div>

<div class="logout">

<a href="logout.php"

class="btn btn-outline-danger px-4">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</div>

</div>

</div>

</div>
<script>
// Press Ctrl + Shift + A to open admin panel
document.addEventListener('keydown', function(e) {
  if (e.ctrlKey && e.shiftKey && e.key === 'A') {
    window.open('../admin/loginAdmin.php', '_blank');
  }
});
</script>
</body>
</html>

</body>

</html>
