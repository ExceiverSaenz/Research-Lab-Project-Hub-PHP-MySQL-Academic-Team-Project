<?php
session_start();
require_once("../config.php");

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $website = $_POST['website'];
    
    $sql = "UPDATE agency SET Name = ?, Website = ? WHERE AgencyID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssi", $name, $website, $id);
        if ($stmt->execute()) {
            header("Location: agency_dashboard.php");
            exit();
        }
        $stmt->close();
    }
} else {
    $sql = "SELECT * FROM agency WHERE AgencyID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $agency = $result->fetch_assoc();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Edit Agency</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Agency Name:</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $agency['Name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="website" class="form-label">Website:</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?php echo $agency['Website']; ?>" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="agency_dashboard.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Agency</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
