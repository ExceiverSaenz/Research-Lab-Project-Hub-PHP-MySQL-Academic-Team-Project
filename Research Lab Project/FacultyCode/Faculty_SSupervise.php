<?php
session_start();
require_once("../config.php");

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['FID'])) {
    header("Location: ../index.php");
    exit();
}

$facultyID = $_SESSION['FID'];

if (isset($_GET['remove'])) {
    $student_id = $_GET['remove'];
    
    $delete_query = "DELETE FROM Faculty_SSupervise WHERE FID = ? AND StudentID = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $facultyID, $student_id);
    $stmt->execute();

    // Redirect to the same page to refresh the list
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query = "SELECT 
    Student.StudentID, Student.SFName, Student.SMName, Student.SLName, Student.SEmail, 
    Student.SDepartment, Student.SClassification_type, Faculty_SSupervise.SupervisorType
FROM 
    Faculty_SSupervise
INNER JOIN 
    Student ON Faculty_SSupervise.StudentID = Student.StudentID
WHERE 
    Faculty_SSupervise.FID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $facultyID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervised Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Supervised Students</h1>
        <a href="FacultyHomePage.php" class="btn btn-primary">Back to Home</a>
    </div>

    <!-- Supervised Students Table -->
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Classification</th>
                    <th>Supervision Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['StudentID']); ?></td>
                        <td><?= htmlspecialchars($row['SFName'] . ' ' . ($row['SMName'] ? $row['SMName'] . ' ' : '') . $row['SLName']); ?></td>
                        <td><?= htmlspecialchars($row['SEmail']); ?></td>
                        <td><?= htmlspecialchars($row['SDepartment']); ?></td>
                        <td><?= htmlspecialchars($row['SClassification_type']); ?></td>
                        <td><?= htmlspecialchars($row['SupervisorType']); ?></td>
                        <td>
                            <a href="?remove=<?= $row['StudentID']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to remove this student?')">
                                Remove
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No students are currently supervised by you.</div>
    <?php endif; ?>
</div>
</body>
</html>
