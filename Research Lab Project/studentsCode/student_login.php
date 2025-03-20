<?php
session_start();
require_once("../config.php");

$error_message = null;

// Check if the user is logged in as student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $query = "SELECT * FROM student WHERE SFName = ? AND Spassword = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ss", $name, $password);
        $stmt->execute();
        $stmt->store_result();


        if ($stmt->num_rows > 0) {
            
            $stmt->close();  

            
            $query = "SELECT StudentID, SFName, SEmail, Spassword FROM student WHERE SFName = ?";
            if ($stmt = $conn->prepare($query)) {
     
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $stmt->store_result();

                // Verify user
                if ($stmt->num_rows > 0) {
                    // Obtain results
                    $stmt->bind_result($StudentID, $SFName, $SEmail, $SPassword);
                    $stmt->fetch(); // Obtener los resultados

                    // Save session
                    $_SESSION['student_logged_in'] = true; // start session
                    $_SESSION['SFName'] = $SFName;         // save name 
                    $_SESSION['SEmail'] = $SEmail;       // save email
                    $_SESSION['SPassword'] = $SPassword; // save password
                    $_SESSION['StudentID'] = $StudentID; // save Student ID in the session
                    echo "Exceiver estuvo aqui \n" . $_SESSION['student_logged_in'] . " " . $_SESSION['SFName'] . " " . $_SESSION['SEmail'] . " " . $_SESSION['SPassword'];
                    header("Location: student_menu.php"); //redirect after login
                    exit;
                } else {
                    $error_message = "Error al recuperar los datos del usuario.";
                }

                $stmt->close();
            }
        } else {
            $error_message = "Invalid name or password.";
        }
    } else {
        $error_message = "Database error: Unable to prepare statement.";
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .student-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        button:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-user-graduate student-icon"></i>
                    <h2 class="mb-4">Student Login</h2>
                </div>

                <!-- Mostrar mensaje de error si existe -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="student_login.php" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-user me-2"></i> name
                        </label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i> Password
                        </label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
