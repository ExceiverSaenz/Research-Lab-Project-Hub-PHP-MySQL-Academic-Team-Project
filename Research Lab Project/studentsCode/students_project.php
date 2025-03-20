<?php
session_start();
require_once("../config.php");

// Check if user is logged in

$StudentID = $_SESSION['StudentID'];

// Query to get projects the student is participating in
$query = "SELECT DISTINCT p.PID, p.PName, p.PObjictive, p.PStartDate, p.PEndDate
          FROM project p
          JOIN participate pa ON p.PID = pa.ProjectID
          WHERE pa.StudentID = ?";

$projects = array();

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $StudentID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #007bff;
            padding: 1rem;
        }
        .navbar-brand {
            color: white;
            font-size: 1.5rem;
        }
        .action-buttons {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100">
                <span class="navbar-brand">
                    <i class="fas fa-project-diagram me-2"></i>
                    Projects for <?php echo htmlspecialchars($_SESSION['user']); ?>
                </span>
                <div class="action-buttons">
                    <a href="join_project.php" class="btn btn-outline-light me-2">Join New Project</a>
                    <a href="student_menu.php" class="btn btn-outline-light">Back to Menu</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php if (empty($projects)): ?>
            <div class="alert alert-info">
                You haven't joined any projects yet. 
                <a href="join_project.php" class="alert-link">Click here to join a project</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Project ID</th>
                            <th>Name</th>
                            <th>Objective</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['PID']); ?></td>
                                <td><?php echo htmlspecialchars($project['PName']); ?></td>
                                <td><?php echo htmlspecialchars($project['PObjictive']); ?></td>
                                <td><?php echo htmlspecialchars($project['PStartDate']); ?></td>
                                <td><?php echo htmlspecialchars($project['PEndDate']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="create_task.php?project_id=<?php echo $project['PID']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-tasks"></i> Tasks
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>