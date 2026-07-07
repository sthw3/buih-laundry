<?php
include("../config/database.php");

if (!isset($_GET['id'])) {
    die("No service ID provided.");
}
$id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("UPDATE service SET Service_Name = ?, Price = ? WHERE Service_ID = ?");
    $stmt->bind_param("sdi", $_POST['service_name'], $_POST['price'], $id);
    $stmt->execute();

    echo "<script>alert('Updated successfully'); window.location='manageService.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM service WHERE Service_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    die("Service not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Service</title>
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
    <h4>Edit Service #<?= htmlspecialchars($id) ?></h4>

    <form method="POST">
        <div class="row">
            <div class="mb-3 col-md-6">
                <label>Service Name</label>
                <input type="text" name="service_name" class="form-control"
                    value="<?= htmlspecialchars($service['Service_Name']) ?>" required>
            </div>

            <div class="mb-3 col-md-6">
                <label>Price</label>
                <input type="number" step="0.01" name="price" class="form-control"
                    value="<?= htmlspecialchars($service['Price']) ?>" required>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="manageService.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>