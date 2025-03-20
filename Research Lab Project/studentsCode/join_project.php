<?php
session_start();
require_once("../config.php");

// Check if user is logged in
//if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['StudentID'])) {
  //  header("Location: ../student_login.php");
    //exit();
//}

$studentID = $_SESSION['StudentID'];

// Handle project joining
if (isset($_POST['join_project']) && isset($_POST['project_id'])) {
    $project_id = intval($_POST['project_id']);
    
    // Check if already participating
    $check_query = "SELECT * FROM participate WHERE StudentID = ? AND ProjectID = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $studentID, $project_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        // Not participating yet, so add them
        $insert_query = "INSERT INTO participate (StudentID, ProjectID) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $studentID, $project_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Successfully joined the project!";
            header("Location: projects_page.php");
            exit();
        } else {
            $_SESSION['error'] = "Error joining project: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "You're already participating in this project.";
    }
    $check_stmt->close();
}

// Get available projects that the student hasn't joined
$query = "SELECT p.* FROM project p 
          WHERE p.PID NOT IN (
              SELECT ProjectID FROM participate WHERE StudentID = ?
          )";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Projects</title>
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
                <span class="navbar-brand">Join Projects</span>
                <div class="action-buttons">
                    <a href="projects_page.php" class="btn btn-outline-light me-2">My Projects</a>
                    <a href="student_menu.php" class="btn btn-outline-light">Menu</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Project ID</th>
                            <th>Name</th>
                            <th>Objective</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['PID']); ?></td>
                                <td><?php echo htmlspecialchars($row['PName']); ?></td>
                                <td><?php echo htmlspecialchars($row['PObjictive']); ?></td>
                                <td><?php echo htmlspecialchars($row['PStartDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['PEndDate']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="project_id" value="<?php echo $row['PID']; ?>">
                                        <button type="submit" name="join_project" class="btn btn-primary btn-sm">Join</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No available projects found to join.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>