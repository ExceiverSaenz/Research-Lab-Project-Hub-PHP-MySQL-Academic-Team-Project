<?php
session_start();
require_once("../config.php");

// Get project reports for agency
$agency_id = $_SESSION['agency_id'];

// Procedures for different reports
function generateProjectReport($conn, $agency_id) {
    $sql = "CALL GetAgencyProjects(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    return $stmt->get_result();
}

function generateStatusReport($conn, $agency_id) {
    $sql = "SELECT p.PID, p.PName, 
            DATEDIFF(p.PEndDate, CURDATE()) as DaysRemaining,
            COUNT(pd.PDeliverable) as Deliverables,
            p.PStartDate, p.PEndDate
            FROM Project p 
            LEFT JOIN p_deliverables pd ON p.PID = pd.PID
            JOIN Manage m ON p.PID = m.PID
            WHERE m.AgencyID = ?
            GROUP BY p.PID";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Handle report generation
if(isset($_GET['report'])) {
    $report_type = $_GET['report'];
    $result = ($report_type === 'status') ? 
              generateStatusReport($conn, $agency_id) : 
              generateProjectReport($conn, $agency_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects for <?php echo $_SESSION['agency_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <h1 class="navbar-brand mb-0">Projects for <?php echo $_SESSION['agency_name']; ?></h1>
            <div>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#reportModal">
                    <i class="fas fa-chart-bar me-2"></i>Generate Report
                </button>
                <a href="add_project.php" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Create New Project
                </a>
                <a href="logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <a href="?report=projects" class="btn btn-primary mb-2 w-100">Project Details Report</a>
                    <a href="?report=status" class="btn btn-primary w-100">Project Status Report</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Display -->
    <?php if(isset($_GET['report'])): ?>
        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><?php echo ucfirst($_GET['report']); ?> Report</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <?php if($_GET['report'] === 'status'): ?>
                                <tr>
                                    <th>Project ID</th>
                                    <th>Name</th>
                                    <th>Days Remaining</th>
                                    <th>Deliverables Count</th>
                                    <th>Timeline</th>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <th>Project ID</th>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Deliverables</th>
                                </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <?php if($_GET['report'] === 'status'): ?>
                                        <td><?php echo $row['PID']; ?></td>
                                        <td><?php echo $row['PName']; ?></td>
                                        <td>
                                            <?php 
                                                $days = $row['DaysRemaining'];
                                                $class = $days < 0 ? 'text-danger' : ($days < 30 ? 'text-warning' : 'text-success');
                                                echo "<span class='$class'>$days days</span>";
                                            ?>
                                        </td>
                                        <td><?php echo $row['Deliverables']; ?></td>
                                        <td>
                                            <?php 
                                                echo $row['PStartDate'] . ' to ' . $row['PEndDate'];
                                            ?>
                                        </td>
                                    <?php else: ?>
                                        <td><?php echo $row['ProjectID']; ?></td>
                                        <td><?php echo $row['ProjectName']; ?></td>
                                        <td><?php echo $row['PStartDate']; ?></td>
                                        <td><?php echo $row['PEndDate']; ?></td>
                                        <td><?php echo $row['Deliverables']; ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>