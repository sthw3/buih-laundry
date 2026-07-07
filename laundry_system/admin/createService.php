<?php
include("../config/database.php");

/* Generate Next Service ID */
$queryID = "SELECT MAX(Service_ID) AS lastID FROM service";
$resultID = mysqli_query($conn, $queryID);

$rowID = mysqli_fetch_assoc($resultID);

if ($rowID['lastID'] == NULL) {
    $newID = 1;
} else {
    $newID = $rowID['lastID'] + 1;
}

/* Insert New Service */
if (isset($_POST['submit'])) {

    $serviceID = $_POST['serviceID'];
    $serviceName = $_POST['serviceName'];
    $price = $_POST['price'];

    $sql = "INSERT INTO service(Service_ID, Service_Name, Price)
            VALUES('$serviceID', '$serviceName', '$price')";

    if (mysqli_query($conn, $sql)) {

        echo "<script>
                alert('Service Added Successfully');
                window.location='manageService.php';
              </script>";

        exit();
    } else {

        echo "<script>
                alert('Failed to Add Service');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Create Service</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fa;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

.form-label{
    font-weight:600;
}

.btn-save{
    background:#253154;
    color:white;
}

.btn-save:hover{
    background:#1b2545;
    color:white;
}

</style>

</head>
<body>

<div class="container">

    <div class="card">

        <div class="card-header">
            <h3>Add New Service</h3>
        </div>

        <div class="card-body">

            <form method="POST">

                <!-- Service ID -->
                <div class="mb-3">
                    <label class="form-label">Service ID</label>

                    <input type="text"
                           class="form-control"
                           name="serviceID"
                           value="<?php echo $newID; ?>"
                           readonly>
                </div>

                <!-- Service Name -->
                <div class="mb-3">
                    <label class="form-label">Service Name</label>

                    <select class="form-select"
                            name="serviceName"
                            required>

                        <option value="">Select Service</option>

                        <option value="Wash & Fold">
                            Wash & Fold
                        </option>

                        <option value="Ironing Service">
                            Ironing Service
                        </option>

                        <option value="Dry Cleaning">
                            Dry Cleaning
                        </option>

                        <option value="Express Laundry (Same Day)">
                            Express Laundry (Same Day)
                        </option>

                        <option value="Blanket / Comforter Wash">
                            Blanket / Comforter Wash
                        </option>

                    </select>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <label class="form-label">Price (RM)</label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           class="form-control"
                           name="price"
                           placeholder="Enter Service Price"
                           required>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">

                    <button type="submit"
                            name="submit"
                            class="btn btn-save">
                        Save Service
                    </button>

                    <a href="manageService.php"
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