<?php
// Inicia la sesión
require_once("config.php");
session_start();


$searchAdmin = "SELECT AEMAIL,APassword FROM admin";


$admins = array();


// Procesar el formulario solo si se envía con el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validar credenciales: Usuario fijo "Adrian", Contraseña "123"
    if ($username === "Adrian" && $password === "123") {
        // Login exitoso: redirigir
        echo "<script>
                alert('Login exitoso');
                window.location.href = 'https://cssrvlab01.utep.edu/Classes/CS4342_5342%20Dr.%20Villanueva/Team16%20NVR/AdminCode/adminDashboard.php';
              </script>";
        exit();
    } else {
        // Credenciales incorrectas
        echo "<script>
                alert('Usuario o contraseña incorrectos');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<h2>Login</h2>
<form method="POST">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>
</body>
</html>
