<?php

session_start();

if(!isset($_SESSION['customer_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

$customer_id = $_SESSION['customer_id'];

$query = mysqli_query($conn,
"SELECT * FROM customer
WHERE Customer_ID='$customer_id'");

$customer = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Update Account</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:700px;
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

.form-label{
    font-weight:bold;
}

.btn-save{
    background:#253154;
    color:white;
}

.btn-save:hover{
    background:#1d2744;
    color:white;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<h3>
✏️ Update Account
</h3>

</div>

<div class="card-body">

<form action="save_update_account.php" method="POST">

<div class="mb-3">

<label class="form-label">
Customer Name
</label>

<input
type="text"
name="customer_name"
class="form-control"
value="<?php echo $customer['Customer_Name']; ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">
Phone Number
</label>

<input
type="text"
name="phone"
class="form-control"
value="<?php echo $customer['Cust_PhoneNum']; ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">
Email
</label>

<input
type="email"
name="email"
class="form-control"
value="<?php echo $customer['Email']; ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">
Address
</label>

<textarea
name="address"
class="form-control"
rows="3"
required><?php echo $customer['Address']; ?></textarea>

</div>

<div class="d-flex gap-2">

<button
type="submit"
class="btn btn-save">

Save Changes

</button>

<a href="view_account.php"
class="btn btn-secondary">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</body>

</html>