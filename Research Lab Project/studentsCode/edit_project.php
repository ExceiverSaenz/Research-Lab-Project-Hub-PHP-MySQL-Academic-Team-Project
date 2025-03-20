<?php
session_start();
require_once("../config.php");

// Check if user is logged in as agency
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agency') {
    header("Location: login.php");
    exit();
}

$project_id = isset($_GET['id']) ? $_GET['id'] : 0;
$agency_id = $_SESSION['agency_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Update project
        $sql = "UPDATE project SET 
                PName = ?, 
                PObjective = ?, 
                PStartDate = ?, 
                PEndDate = ? 
                WHERE PID = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            $_POST['project_name'],
            $_POST['project_objective'],
            $_POST['start_date'],
            $_POST['end_date'],
            $project_id
        );
        $stmt->execute();

        // Delete old deliverables
        $sql = "DELETE FROM p_deliverables WHERE PID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();

        // Insert new deliverables
        if (!empty($_POST['deliverables'])) {
            $deliv_sql = "INSERT INTO p_deliverables (PID, PDeliverable, PDeadline) VALUES (?, ?, ?)";
            $deliv_stmt = $conn->prepare($deliv_sql);
            
            foreach ($_POST['deliverables'] as $key => $deliverable) {
                if (!empty($deliverable) && !empty($_POST['deadlines'][$key])) {
                    $deliv_stmt->bind_param("iss", $project_id, $deliverable, $_POST['deadlines'][$key]);
                    $deliv_stmt->execute();
                }
            }
        }

        $conn->commit();
        header("Location: agencyprojects.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error updating project: " . $e->getMessage();
    }
}

// Get project data
$sql = "SELECT p.*, GROUP_CONCAT(pd.PDeliverable) as deliverables, 
        GROUP_CONCAT(pd.PDeadline) as deadlines
        FROM project p
        LEFT JOIN p_deliverables pd ON p.PID = pd.PID
        WHERE p.PID = ? 
        GROUP BY p.PID";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

// Get deliverables
$deliv_sql = "SELECT * FROM p_deliverables WHERE PID = ?";
$deliv_stmt = $conn->prepare($deliv_sql);
$deliv_stmt->bind_param("i", $project_id);
$deliv_stmt->execute();
$deliverables = $deliv_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Project</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Project Name:</label>
                        <input type="text" class="form-control" name="project_name" 
                               value="<?php echo htmlspecialchars($project['PName']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Project Objective:</label>
                        <textarea class="form-control" name="project_objective" rows="3" required>
                            <?php echo htmlspecialchars($project['PObjective']); ?>
                        </textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date:</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="<?php echo $project['PStartDate']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date:</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="<?php echo $project['PEndDate']; ?>" required>
                        </div>
                    </div>

                    <h4 class="mt-4">Deliverables</h4>
                    <div id="deliverables-container">
                        <?php foreach ($deliverables as $deliverable): ?>
                            <div class="deliverable-row mb-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="deliverables[]" 
                                               value="<?php echo htmlspecialchars($deliverable['PDeliverable']); ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="date" class="form-control" name="deadlines[]" 
                                               value="<?php echo $deliverable['PDeadline']; ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-deliverable">×</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn btn-success mb-3" id="add-deliverable">
                        <i class="fas fa-plus me-2"></i>Add Deliverable
                    </button>

                    <div class="d-flex justify-content-between">
                        <a href="agencyprojects.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('add-deliverable').addEventListener('click', function() {
            const container = document.getElementById('deliverables-container');
            const template = `
                <div class="deliverable-row mb-2">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="deliverables[]">
                        </div>
                        <div class="col-md-5">
                            <input type="date" class="form-control" name="deadlines[]">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-deliverable">×</button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-deliverable')) {
                const rows = document.querySelectorAll('.deliverable-row');
                if (rows.length > 1) {
                    e.target.closest('.deliverable-row').remove();
                }
            }
        });
    </script>
</body>
</html>