<?php
session_start();
require_once("../config.php");

// Get agencies with their projects
$query = "SELECT a.AgencyID, a.Name, a.Website, a.agencyAuthenticated, 
          COUNT(DISTINCT p.PID) as project_count
          FROM agency a 
          LEFT JOIN Manage m ON a.AgencyID = m.AgencyID
          LEFT JOIN project p ON m.PID = p.PID
          GROUP BY a.AgencyID, a.Name, a.Website, a.agencyAuthenticated";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .badge-verified {
            background-color: #198754;
        }
        .badge-unverified {
            background-color: #dc3545;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    Agency Management
                </h3>
                <div>
                    <a href="add_project.php" class="btn btn-light me-2">
                        <i class="fas fa-plus me-2"></i>Create New Project
                    </a>
                    <a href="add_agency.php" class="btn btn-light">
                        <i class="fas fa-plus me-2"></i>Add Agency
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Agency Name</th>
                                <th>Website</th>
                                <th>Status</th>
                                <th>Projects</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $row['AgencyID']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo $row['Name']; ?></div>
                                    </td>
                                    <td>
                                        <a href="<?php echo $row['Website']; ?>" 
                                           class="text-decoration-none" 
                                           target="_blank">
                                            <?php echo $row['Website']; ?>
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $row['agencyAuthenticated'] ? 'badge-verified' : 'badge-unverified'; ?>">
                                            <?php echo $row['agencyAuthenticated'] ? 'Verified' : 'Unverified'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view_projects.php?agency_id=<?php echo $row['AgencyID']; ?>" 
                                           class="btn btn-info btn-sm text-white">
                                            <i class="fas fa-tasks me-1"></i>
                                            Projects (<?php echo $row['project_count']; ?>)
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_agency.php?id=<?php echo $row['AgencyID']; ?>" 
                                               class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($row['agencyAuthenticated']): ?>
                                                <a href="?unverify_id=<?php echo $row['AgencyID']; ?>" 
                                                   class="btn btn-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?verify_id=<?php echo $row['AgencyID']; ?>" 
                                                   class="btn btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="delete_agency.php?id=<?php echo $row['AgencyID']; ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this agency? This will also delete all associated projects.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No agencies found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Handle agency verification/unverification
    if (isset($_GET['verify_id'])) {
        $agency_id = $_GET['verify_id'];
        $sql = "UPDATE agency SET agencyAuthenticated = TRUE WHERE AgencyID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $agency_id);
            $stmt->execute();
            echo "<script>window.location.href = 'agency_dashboard.php';</script>";
        }
    }

    if (isset($_GET['unverify_id'])) {
        $agency_id = $_GET['unverify_id'];
        $sql = "UPDATE agency SET agencyAuthenticated = FALSE WHERE AgencyID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $agency_id);
            $stmt->execute();
            echo "<script>window.location.href = 'agency_dashboard.php';</script>";
        }
    }
    ?>
</body>
</html>