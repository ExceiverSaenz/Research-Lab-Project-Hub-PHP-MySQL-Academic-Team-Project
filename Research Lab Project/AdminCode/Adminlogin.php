<?php
session_start(); // Iniciar la sesión
require_once("../config.php");

$error_message = null;

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = isset($_POST['userName']) ? $_POST['userName'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Consulta SQL para verificar si el usuario existe en la vista searchadminview
    $query = "SELECT AName FROM searchadminview WHERE AName = ? AND APassword = ?";
    if ($stmt = $conn->prepare($query)) {
        // Vincular parámetros
        $stmt->bind_param("ss", $userName, $password);
        $stmt->execute();
        $stmt->store_result();

        // Verificar si se encontró el usuario
        if ($stmt->num_rows > 0) {
            // El usuario existe, ahora obtener los datos de la vista saveAdminSession
            $stmt->close();  // Cerrar la primera declaración

            // Consulta SQL para obtener los datos desde la vista saveAdminSession
            $query = "SELECT AName, AName, APassword FROM saveAdminSession WHERE AName = ?";
            if ($stmt = $conn->prepare($query)) {
                // Vincular parámetro
                $stmt->bind_param("s", $userName);
                $stmt->execute();
                $stmt->store_result();

                // Verificar si se encontró el usuario
                if ($stmt->num_rows > 0) {
                    // Obtener los resultados de la consulta
                    $stmt->bind_result($AName, $AEmail, $APassword);
                    $stmt->fetch(); // Obtener los resultados

                    // Guardar los datos en la sesión
                    $_SESSION['admin_logged_in'] = true; // Establecer la sesión
                    $_SESSION['AName'] = $AName;         // Guardar el nombre en la sesión
                    $_SESSION['AEmail'] = $AEmail;       // Guardar el correo electrónico en la sesión
                    $_SESSION['APassword'] = $APassword; // Guardar la contraseña en la sesión

                    header("Location: adminDashboard.php"); // Redirigir después de iniciar sesión
                    exit;
                } else {
                    $error_message = "Error al recuperar los datos del usuario.";
                }

                $stmt->close();
            }
        } else {
            $error_message = "Invalid userName or password.";
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
    <title>Admin Login</title>
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
        .admin-icon {
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
                    <i class="fas fa-user-shield admin-icon"></i>
                    <h2 class="mb-4">Admin Login</h2>
                </div>

                <!-- Mostrar mensaje de error si existe -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="userName" class="form-label">
                            <i class="fas fa-user me-2"></i> userName
                        </label>
                        <input type="text" id="userName" name="userName" class="form-control" required>
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
