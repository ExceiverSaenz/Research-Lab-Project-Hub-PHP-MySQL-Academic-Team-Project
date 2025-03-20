<?php
session_start();
require_once("../config.php");

$agency_id = isset($_GET['agency_id']) ? (int)$_GET['agency_id'] : 0;

// Get agency details
$agency_query = "SELECT Name FROM agency WHERE AgencyID = ?";
if ($stmt = $conn->prepare($agency_query)) {
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $agency_result = $stmt->get_result();
    $agency = $agency_result->fetch_assoc();
    $stmt->close();
}

// Get projects for this agency
$projects_query = "SELECT p.*, 
    (SELECT GROUP_CONCAT(CONCAT(pd.PDeliverable, ' (Due: ', pd.PDeadline, ')') SEPARATOR '\n')
     FROM p_deliverables pd 
     WHERE pd.PID = p.PID) as deliverables
    FROM project p
    JOIN Manage m ON p.PID = m.PID
    WHERE m.AgencyID = ?";

if ($stmt = $conn->prepare($projects_query)) {
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $projects_result = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>
                    Projects for <?php echo htmlspecialchars($agency['Name']); ?>
                </h3>
                <a href="add_project.php?agency_id=<?php echo $agency_id; ?>" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Add Project
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Objective</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Deliverables</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($project = $projects_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['PName']); ?></td>
                                    <td><?php echo htmlspecialchars($project['PObjective']); ?></td>
                                    <td><?php echo $project['PStartDate']; ?></td>
                                    <td><?php echo $project['PEndDate']; ?></td>
                                    <td>
                                        <pre class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($project['deliverables']); ?></pre>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit_project.php?id=<?php echo $project['PID']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_project.php?id=<?php echo $project['PID']; ?
<a href="delete_project.php?id=<?php echo $project['PID']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this project?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>