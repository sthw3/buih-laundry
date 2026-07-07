<?php
include("../config/database.php");

/* Generate Next Staff ID */
$queryID = "SELECT MAX(Staff_ID) AS lastID FROM staff";
$resultID = mysqli_query($conn, $queryID);

$rowID = mysqli_fetch_assoc($resultID);

if ($rowID['lastID'] == NULL) {
    $newID = 1;
} else {
    $newID = $rowID['lastID'] + 1;
}

/* Save Staff */
if(isset($_POST['submit']))
{
    $staffID = $_POST['staffID'];
    $staffName = $_POST['staffName'];
    $staffPhoneNum = $_POST['staffPhoneNum'];
    $staffEmail = $_POST['staffEmail'];

    $sql = "INSERT INTO staff
            (Staff_ID, Staff_Name, Staff_PhoneNum, Staff_Email)
            VALUES
            ('$staffID', '$staffName', '$staffPhoneNum', '$staffEmail')";

    if(mysqli_query($conn, $sql))
    {
        echo "<script>
                alert('Staff Added Successfully');
                window.location='manageStaff.php';
              </script>";
        exit();
    }
    else
    {
        echo "<script>
                alert('Failed to Add Staff');
              </script>";
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Create Staff</title>

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
        <h3>Add New Staff</h3>
    </div>

    <div class="card-body">

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Staff ID</label>

                <input type="text"
                       class="form-control"
                       name="staffID"
                       value="<?php echo $newID; ?>"
                       readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Staff Name</label>

                <input type="text"
                       class="form-control"
                       name="staffName"
                       placeholder="Enter Staff Name"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>

                <input type="text"
                       class="form-control"
                       name="staffPhoneNum"
                       placeholder="Enter Phone Number"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>

                <input type="email"
                       class="form-control"
                       name="staffEmail"
                       placeholder="Enter Email Address"
                       required>
            </div>

            <div class="d-flex gap-2">

                <button type="submit"
                        name="submit"
                        class="btn btn-save">
                    Save Staff
                </button>

                <a href="manageStaff.php"
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
