<?php
include("../config/database.php");

/* Generate Next Customer ID */
$queryID = "SELECT MAX(Customer_ID) AS lastID FROM customer";
$resultID = mysqli_query($conn, $queryID);

$rowID = mysqli_fetch_assoc($resultID);

if ($rowID['lastID'] == NULL) {
    $newID = 1;
} else {
    $newID = $rowID['lastID'] + 1;
}

/* Save Customer */
if(isset($_POST['submit']))
{
    $customerID = $_POST['customerID'];
    $customerName = $_POST['customerName'];
    $phoneNum = $_POST['phoneNum'];
    $email = $_POST['email'];
    $address = $_POST['address'];

   $placeholderPassword = password_hash(uniqid(), PASSWORD_DEFAULT);

    $sql = "INSERT INTO customer
            (Customer_ID, Customer_Name, Cust_PhoneNum, Email, Address, Customer_Password)
            VALUES
            ('$customerID', '$customerName', '$phoneNum', '$email', '$address', '$placeholderPassword')";

    if(mysqli_query($conn, $sql))
    {
        echo "<script>
                alert('Customer Added Successfully');
                window.location='manageCustomer.php';
              </script>";
        exit();
    }
    else
    {
        echo "<script>
                alert('Failed to Add Customer');
              </script>";
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Create Customer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container{
    max-width:800px;
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

.form-label{
    font-weight:600;
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
        <h3>Add New Customer</h3>
    </div>

    <div class="card-body">

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Customer ID</label>

                <input type="text"
                       class="form-control"
                       name="customerID"
                       value="<?php echo $newID; ?>"
                       readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Customer Name</label>

                <input type="text"
                       class="form-control"
                       name="customerName"
                       placeholder="Enter Customer Name"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>

                <input type="text"
                       class="form-control"
                       name="phoneNum"
                       placeholder="Enter Phone Number"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>

                <input type="email"
                       class="form-control"
                       name="email"
                       placeholder="Enter Email"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>

                <textarea class="form-control"
                          name="address"
                          rows="4"
                          placeholder="Enter Address"
                          required></textarea>
            </div>

            <div class="d-flex gap-2">

                <button type="submit"
                        name="submit"
                        class="btn btn-save">
                    Save Customer
                </button>

                <a href="manageCustomer.php"
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
