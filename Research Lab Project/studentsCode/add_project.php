<?php
session_start();
require_once("../config.php");

// Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as agency
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agency') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get form data
        $project_id = $_POST['pid']; // Added semicolon here
        $project_name = $_POST['project_name'];
        $objective = $_POST['project_objective'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $agency_id = $_SESSION['agency_id'];

        // Insert project - fixed bind_param to include the PID
        $sql = "INSERT INTO project (PID, PName, PObjective, PStartDate, PEndDate) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $project_id, $project_name, $objective, $start_date, $end_date);
        $stmt->execute();

        // Link project to agency in Manage table - use the submitted PID
        $manage_sql = "INSERT INTO Manage (PID, AgencyID) VALUES (?, ?)";
        $manage_stmt = $conn->prepare($manage_sql);
        $manage_stmt->bind_param("ii", $project_id, $agency_id);
        $manage_stmt->execute();

        // Handle deliverables
        if (!empty($_POST['deliverables'])) {
            $deliv_sql = "INSERT INTO p_deliverables (PID, PDeliverable, PDeadline) VALUES (?, ?, ?)";
            $deliv_stmt = $conn->prepare($deliv_sql);
            
            foreach ($_POST['deliverables'] as $key => $deliverable) {
                if (!empty($deliverable) && !empty($_POST['deadlines'][$key])) {
                    $deadline = $_POST['deadlines'][$key];
                    $deliv_stmt->bind_param("iss", $project_id, $deliverable, $deadline);
                    $deliv_stmt->execute();
                }
            }
        }

        // Commit and redirect
        if ($conn->commit()) {
            echo "<script>
                alert('Project created successfully!');
                window.location.href = 'agencyprojects.php';
            </script>";
            exit();
        }

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error_message = "Error creating project: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .deliverable-row { margin-bottom: 10px; }
        .btn-remove-deliverable { padding: 5px 10px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Project</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST" id="projectForm">
                    <!-- Agency Information -->
                    <div class="mb-3">
                        <label class="form-label">Agency Name:</label>
                        <input type="text" class="form-control" value="<?php echo $_SESSION['agency_name']; ?>" readonly>
                    </div>

                    <!-- Project ID field -->
                    <div class="mb-3">
                        <label class="form-label">Project ID:</label>
                        <input type="number" class="form-control" name="pid" required>
                    </div>

                    <!-- Project Details -->
                    <div class="mb-3">
                        <label class="form-label">Project Name:</label>
                        <input type="text" class="form-control" name="project_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Project Objective:</label>
                        <textarea class="form-control" name="project_objective" rows="3" required></textarea>
                    </div>

                    <!-- Project Dates -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date:</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date:</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>

                    <!-- Deliverables Section -->
                    <h4 class="mt-4 mb-3">Deliverables</h4>
                    <div id="deliverables-container">
                        <div class="deliverable-row">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="deliverables[]" placeholder="Deliverable description">
                                </div>
                                <div class="col-md-5">
                                    <input type="date" class="form-control" name="deadlines[]">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-remove-deliverable">×</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success mb-4" id="add-deliverable">
                        <i class="fas fa-plus me-2"></i>Add Another Deliverable
                    </button>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="agencyprojects.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add deliverable row
            document.getElementById('add-deliverable').addEventListener('click', function() {
                const container = document.getElementById('deliverables-container');
                const newRow = container.querySelector('.deliverable-row').cloneNode(true);
                newRow.querySelectorAll('input').forEach(input => input.value = '');
                container.appendChild(newRow);
            });

            // Remove deliverable row
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-deliverable')) {
                    const rows = document.querySelectorAll('.deliverable-row');
                    if (rows.length > 1) {
                        e.target.closest('.deliverable-row').remove();
                    }
                }
            });

            // Form validation
            document.getElementById('projectForm').addEventListener('submit', function(e) {
                const startDate = new Date(document.querySelector('input[name="start_date"]').value);
                const endDate = new Date(document.querySelector('input[name="end_date"]').value);

                if (endDate < startDate) {
                    e.preventDefault();
                    alert('End date cannot be earlier than start date');
                }
            });
        });
    </script>
</body>
</html>