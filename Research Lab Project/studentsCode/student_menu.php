<?php
session_start();
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] == false) {
    // Redirigir al usuario a la página de inicio de sesión
    header('Location: student_login.php');
    exit();
}

error_log("Reached student_menu.php");
error_log("Session data: " . print_r($_SESSION, true));
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Previous head content remains the same -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Previous styles remain the same */
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-top: 50px;
        }
        .menu-link {
            display: block;
            padding: 15px;
            margin: 10px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
            color: #0d6efd;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .menu-link:hover {
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
        }
        .user-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .menu-section {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .menu-section:last-child {
            border-bottom: none;
        }
        .section-title {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Student Menu</h1>
        
        <!-- Display user information -->
        <div class="user-info">
            <h5>Welcome, <?php echo htmlspecialchars($_SESSION['SFName']); ?>!</h5>
            <p>Student ID: <?php echo htmlspecialchars($_SESSION['SFName']); ?></p>
        </div>
        <!-- Menu Options -->
        <div class="menu-options">
            <!-- Projects Section -->
            <div class="menu-section">
                <div class="section-title">Projects</div>
                <a href="projects_page.php" class="menu-link">
                    <i class="fas fa-project-diagram me-2"></i> View Projects
                </a>
                <a href="join_project.php" class="menu-link">
                    <i class="fas fa-user-plus me-2"></i> Join Project
                </a>
            </div>
            <!-- Tasks Section -->
            <div class="menu-section">
                <div class="section-title">Tasks Management</div>
                <a href="project_tasks.php" class="menu-link">
                    <i class="fas fa-tasks me-2"></i> View Project Tasks
                </a>
                <a href="create_task.php" class="menu-link">
                    <i class="fas fa-plus-circle me-2"></i> Create New Task
                </a>
                <a href="delete_task.php" class="menu-link">
                    <i class="fas fa-trash-alt me-2"></i> Delete Tasks
                </a>
            </div>
            <!-- Account Section -->
            <div class="menu-section">
                <div class="section-title">Account</div>
                <a href="../logout.php" class="menu-link text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
    </div>
       
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>