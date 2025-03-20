<?php
session_start();
require_once("../config.php");

// Check if the user is logged in and is a faculty member
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['FID'])) {
    header("Location: ../index.php");
    exit();
}

$facultyID = $_SESSION['FID']; // Faculty ID from session

// Query to fetch projects the faculty is participating in
$query = "
    SELECT 
        Project.PID, Project.PName, Project.PObjective, Project.PStartDate, Project.PEndDate
    FROM 
        faculty_psupervise
    INNER JOIN 
        Project ON faculty_psupervise.PID = Project.PID
    WHERE 
        faculty_psupervise.FID = ?";
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
    <title>Projects You're Participating In</title>
    <link rel="icon" type="image/x-icon" href="../logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #343a40;
            color: white;
        }
        .header img {
            height: 60px;
            object-fit: cover;
        }
        .logout-button {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<!-- Header -->
<div class="header">
    <span><?php echo htmlspecialchars($_SESSION['user'] ?? "Faculty Member"); ?></span>
    <img src="../building3.png" alt="Building">
    <form action="/Classes/CS4342_5342 Dr. Villanueva/Team16 NVR/FacultyCode/FacultyHomePage.php" method="post" style="display: inline;">
        <button type="submit" class="logout-button">Home</button>
    </form>
</div>

<!-- Main Content -->
<div class="container mt-4">
    <h1>Projects You're Participating In</h1>
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Project ID</th>
                <th>Project Name</th>
                <th>Objective</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['PID']); ?></td>
                    <td><?= htmlspecialchars($row['PName']); ?></td>
                    <td><?= htmlspecialchars($row['PObjective']); ?></td>
                    <td><?= htmlspecialchars($row['PStartDate']); ?></td>
                    <td><?= htmlspecialchars($row['PEndDate']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No projects found.</p>
    <?php endif; ?>
</div>
</body>
</html>
