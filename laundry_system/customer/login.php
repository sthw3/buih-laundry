<?php
session_start();

if(isset($_SESSION['customer_id']))
{
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Customer Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container{
    max-width:600px;
    margin-top:80px;
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

.btn-login{
    background:#253154;
    color:white;
}

.btn-login:hover{
    background:#1d2744;
    color:white;
}


.eye-btn{

    background:#fff;
    border:1px solid #ced4da;
    border-left:none;

    color:#6c757d;

}

.eye-btn:hover{

    background:#fff;
    color:#253154;

}

.eye-btn:focus{

    box-shadow:none;

}

.input-group .form-control:focus{

    box-shadow:none;

}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<h3>🔐 Customer Login</h3>

</div>

<div class="card-body">

<form action="login_process.php" method="POST">

<div class="mb-3">

<label class="form-label">
Email
</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">
Password
</label>

<div class="input-group">

<input
type="password"
name="password"
id="password"
class="form-control"
required>

<button
class="btn eye-btn"
type="button"
onclick="togglePassword()">

<i class="bi bi-eye" id="eyeIcon"></i>

</button>

</div>

</div>

<div class="d-flex gap-2 mt-4">

<button
type="submit"
class="btn btn-login">

Login

</button>

<a href="register.php"
class="btn btn-success">

Register

</a>

<a href="../index.php"
class="btn btn-secondary">

Home

</a>

</div>

</form>

</div>

</div>

</div>

<script>

function togglePassword()
{
    const password = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");

    if(password.type === "password")
    {
        password.type = "text";
        eyeIcon.classList.remove("bi-eye");
        eyeIcon.classList.add("bi-eye-slash");
    }
    else
    {
        password.type = "password";
        eyeIcon.classList.remove("bi-eye-slash");
        eyeIcon.classList.add("bi-eye");
    }
}

</script>

</body>

</html>