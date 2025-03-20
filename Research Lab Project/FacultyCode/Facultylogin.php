<?php
session_start(); // Iniciar la sesión
require_once("../config.php");

$error_message = null;

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Consulta SQL para verificar las credenciales del faculty
    $query = "SELECT * FROM faculty WHERE FEmail = ?";
    if ($stmt = $conn->prepare($query)) {
        // Vincular parámetros
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si se encontró el usuario
        if ($result->num_rows > 0) {
            $faculty = $result->fetch_assoc();

            // Verificar si el hash de la contraseña existe
            if (isset($faculty['Fpassword']) && !empty($faculty['Fpassword'])) {
                // Verificar la contraseña usando password_verify
                if (password_verify($password, $faculty['Fpassword'])) {
                    // Guardar los datos en la sesión
                    $_SESSION['faculty_logged_in'] = true;
                    $_SESSION['FID'] = $faculty['FID'];
                    $_SESSION['FName'] = $faculty['FName'];
                    $_SESSION['FEmail'] = $faculty['FEmail'];
                    $_SESSION['FDepartment'] = $faculty['FDepartment'];
                    $_SESSION['FTitle'] = $faculty['FTitle'];

                    header("Location: FacultyHomePage.php"); // Redirigir después de iniciar sesión
                    exit;
                } else {
                    $error_message = "Contraseña incorrecta.";
                }
            } else {
                $error_message = "No se encontró un hash de contraseña válido en la base de datos.";
            }
        } else {
            $error_message = "Correo electrónico no encontrado.";
        }

        $stmt->close();
    } else {
        $error_message = "Error de base de datos: No se pudo preparar la consulta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Faculty</title>
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
        .faculty-icon {
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
                    <i class="fas fa-chalkboard-teacher faculty-icon"></i>
                    <h2 class="mb-4">Inicio de Sesión - Faculty</h2>
                </div>

                <!-- Mostrar mensaje de error si existe -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i> Correo Electrónico
                        </label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i> Contraseña
                        </label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
