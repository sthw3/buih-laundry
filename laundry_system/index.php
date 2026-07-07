<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Laundry Booking System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    height:100vh;
    background:linear-gradient(135deg,#253154 0%,#3d4f7c 100%);
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
    overflow:hidden;
    position:relative;
}

body::before{
    content:"";
    position:absolute;
    width:350px;
    height:350px;
    background:rgba(255,255,255,0.08);
    border-radius:50%;
    top:-100px;
    left:-100px;
}

body::after{
    content:"";
    position:absolute;
    width:450px;
    height:450px;
    background:rgba(255,255,255,0.05);
    border-radius:50%;
    bottom:-150px;
    right:-150px;
}

.hero{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    position:relative;
    z-index:1;
}

.card-custom{
    width:700px;
    border:none;
    border-radius:25px;
    padding:40px;
    background:rgba(255,255,255,0.98);
    box-shadow:0 20px 50px rgba(0,0,0,0.25);
    transition:.3s;
}

.card-custom:hover{
    transform:translateY(-5px);
}

.logo-icon{
    width:80px;
    height:80px;
    background:#253154;
    color:white;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:2.2rem;
    margin:0 auto 15px;
}

.main-title{
    color:#253154;
    font-size:2.4rem;
    font-weight:700;
}

.subtitle{
    color:#6c757d;
    margin-top:10px;
    margin-bottom:25px;
}

.feature-box{
    display:flex;
    justify-content:center;
    gap:20px;
    margin-bottom:30px;
    flex-wrap:wrap;
}

.feature-item{
    background:#f8f9fa;
    padding:12px 18px;
    border-radius:12px;
    min-width:170px;
}

.feature-item i{
    color:#253154;
    font-size:1.2rem;
}

.btn-register{
    background:#198754;
    color:white;
    border:none;
    padding:12px 28px;
    border-radius:12px;
}

.btn-register:hover{
    background:#157347;
    color:white;
}

.btn-login{
    background:#253154;
    color:white;
    border:none;
    padding:12px 28px;
    border-radius:12px;
}

.btn-login:hover{
    background:#1d2744;
    color:white;
}

.footer-text{
    margin-top:25px;
    color:#6c757d;
    font-size:0.9rem;
}

</style>

</head>

<body>

<div class="hero">

<div class="card card-custom text-center">

<div class="logo-icon">
<i class="bi bi-basket2-fill"></i>
</div>

<h2 class="main-title">
Laundry Booking System
</h2>

<p class="subtitle">
Create an account or login to manage your laundry booking easily.
</p>

<div class="feature-box">

<div class="feature-item">
<i class="bi bi-person-plus-fill"></i>
<div class="mt-2">Register Account</div>
</div>

<div class="feature-item">
<i class="bi bi-box-arrow-in-right"></i>
<div class="mt-2">Secure Login</div>
</div>

<div class="feature-item">
<i class="bi bi-calendar-check-fill"></i>
<div class="mt-2">Manage Booking</div>
</div>

</div>

<div class="d-flex justify-content-center gap-3 flex-wrap">

<a href="customer/register.php"
class="btn btn-register btn-lg">

<i class="bi bi-person-plus-fill me-2"></i>

Register Account

</a>

<a href="customer/login.php"
class="btn btn-login btn-lg">

<i class="bi bi-box-arrow-in-right me-2"></i>

Login Account

</a>

</div>

<div class="footer-text">

Fast • Reliable • Convenient Laundry Service

</div>

</div>

</div>

</body>

</html>