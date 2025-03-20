<?php
session_start();
require_once("../config.php");

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['FID'])) {
    header("Location: ../index.php");
    exit();
}

// Manejar la unión al proyecto
if (isset($_POST['join_project']) && isset($_POST['project_id'])) {
    $project_id = intval($_POST['project_id']);
    $faculty_id = intval($_SESSION['FID']); // ID de facultad de la sesión

    // Verificar si ya existe la relación para evitar duplicados
    $check_query = "SELECT * FROM faculty_psupervise WHERE FID = ? AND PID = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $faculty_id, $project_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        // No existe la relación, entonces insertar
        $insert_query = "INSERT INTO faculty_psupervise (FID, PID) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $faculty_id, $project_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Te has unido al proyecto exitosamente.";
        } else {
            $_SESSION['error'] = "Error al unirse al proyecto: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Ya estás asignado a este proyecto.";
    }

    $check_stmt->close();

    // Redirigir para evitar reenvío de formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Consulta para obtener los proyectos
$query = "SELECT * FROM Project";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Projects</title>
    <link rel="icon" type="image/x-icon" href="../logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #343a40;
            color: white;
        }
        .header img {
            height: 60px;
            object-fit: cover;
        }
        .logout-button {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<!-- Header -->
<div class="header">
    <span><?php echo htmlspecialchars($_SESSION['user'] ?? "Faculty Member"); ?></span>
    <img src="../building3.png" alt="Building">
    <form action="/Classes/CS4342_5342 Dr. Villanueva/Team16 NVR/FacultyCode/FacultyHomePage.php" method="post" style="display: inline;">
        <button type="submit" class="logout-button">Home</button>
    </form>
</div>

<!-- Main Content -->
<div class="container mt-4">
    <h1>Available Projects</h1>

    <!-- Mensajes de éxito o error -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>PID</th>
                <th>Project Name</th>
                <th>Objective</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['PID']); ?></td>
                    <td><?= htmlspecialchars($row['PName']); ?></td>
                    <td><?= htmlspecialchars($row['PObjective']); ?></td>
                    <td><?= htmlspecialchars($row['PStartDate']); ?></td>
                    <td><?= htmlspecialchars($row['PEndDate']); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="project_id" value="<?= $row['PID']; ?>">
                            <button type="submit" name="join_project" class="btn btn-primary">Join</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No projects available.</p>
    <?php endif; ?>
</div>
</body>
</html>