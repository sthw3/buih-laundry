<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No customer ID provided.");
}
$id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("UPDATE customer SET Customer_Name = ?, Cust_PhoneNum = ?, Email = ?, Address = ? WHERE Customer_ID = ?");
    $stmt->bind_param("ssssi", $_POST['customer_name'], $_POST['cust_phonenum'], $_POST['email'], $_POST['address'], $id);
    $stmt->execute();

    echo "<script>alert('Updated successfully'); window.location='manageCustomer.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM customer WHERE Customer_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    die("Customer not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Customer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body { background:#f5f7fa; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
.form-container {
    max-width: 900px;
    margin: 50px auto;
    background: #fff;
    padding: 35px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.form-container h4 { color: #253154; margin-bottom: 25px; }
.btn-primary { background:#253154; border-color:#253154; }
.btn-primary:hover { background:#1a2340; border-color:#1a2340; }
</style>
</head>
<body>

<div class="form-container">
    <h4>Edit Customer #<?= htmlspecialchars($id) ?></h4>

    <form method="POST">
        <div class="row">
            <div class="mb-3 col-md-6">
                <label>Customer Name</label>
                <input type="text" name="customer_name" class="form-control"
                    value="<?= htmlspecialchars($customer['Customer_Name']) ?>" required>
            </div>

            <div class="mb-3 col-md-6">
                <label>Phone Number</label>
                <input type="tel" name="cust_phonenum" class="form-control"
                    value="<?= htmlspecialchars($customer['Cust_PhoneNum']) ?>" required>
            </div>

            <div class="mb-3 col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($customer['Email']) ?>" required>
            </div>

             <div class="mb-3 col-md-6">
                <label>Address</label>
                <input type="text" name="address" class="form-control"
                    value="<?= htmlspecialchars($customer['Address']) ?>" required>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="manageCustomer.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>