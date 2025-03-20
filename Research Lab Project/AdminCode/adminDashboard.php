<?php
session_start();

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminLogin.php");
    exit;
}

require_once("../config.php"); // Configuración para la conexión a la base de datos

// Cambiar UserName
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_username'])) {
    // Capturar los valores ingresados en el formulario
    $currentPassword = $_POST['current_password_username'];
    $newUsername = $_POST['new_username'];
    $actualMail = $_SESSION['AEmail']; // Correo electrónico almacenado en la sesión

    // Validar si la contraseña actual es correcta (comparar directamente con la base de datos)
    $stmt = $conn->prepare("SELECT APassword FROM admin WHERE AEmail = ?");
    $stmt->bind_param("s", $actualMail); // Usamos el correo para encontrar el usuario
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verificar si la contraseña ingresada coincide con la almacenada
    if ($currentPassword === $storedPassword) {
        // Si la contraseña es correcta, proceder a cambiar el nombre de usuario
        $stmt = $conn->prepare("CALL changeUser(?, ?)");
        $stmt->bind_param("ss", $newUsername, $_SESSION['AName']); // 'AName' es el nombre actual de usuario en la sesión
        if ($stmt->execute()) {
            // Si la ejecución es exitosa, actualizar el nombre de usuario en la sesión
            $_SESSION['AName'] = $newUsername; // Actualizar el nombre de usuario en la sesión
            $_SESSION['username_change_success'] = true; // Mensaje de éxito
            header("Location: adminDashboard.php"); // Redirigir a dashboard (o donde desees)
            exit;
        } else {
            // Si hubo un error, mostrar un mensaje
            $username_error_message = "Error al cambiar el nombre de usuario.";
        }
        $stmt->close();
    } else {
        // Si la contraseña no es correcta, mostrar mensaje de error
        $username_error_message = "La contraseña actual es incorrecta.";
    }
}

// Cambiar AEmail del admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_email'])) {
    // Capturar datos del formulario
    $currentPassword = $_POST['current_password_email'];
    $newEmail = $_POST['new_email'];
    $oldEmail = $_SESSION['AEmail']; // Correo actual del usuario autenticado

    // Validar contraseña (suponiendo que la conexión está en $conn)
    $stmt = $conn->prepare("SELECT APassword FROM admin WHERE AEmail = ?");
    $stmt->bind_param("s", $oldEmail);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    if ($currentPassword != $storedPassword) {
        echo "Invalid password. Please try again.";
    } else {
        // Llamar al procedimiento almacenado para cambiar el correo
        $stmt = $conn->prepare("CALL changeMail(?, ?)");
        $stmt->bind_param("ss", $newEmail, $oldEmail);

        if ($stmt->execute()) {
            echo "Email updated successfully!";
            // Actualizar la sesión con el nuevo correo
            $_SESSION['AEmail'] = $newEmail;
        } else {
            echo "Error updating email: " . $conn->error;
        }

        $stmt->close();
    }
}

// Cambiar APassword de admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Verificar si el nuevo password y el de confirmación coinciden
    if ($newPassword !== $confirmPassword) {
        echo "New password and confirmation do not match.";
        return;
    }

    // Obtener el nombre del administrador actual (suponiendo que está en la sesión)
    $adminName = $_SESSION['AName'];

    // Validar la contraseña actual
    $stmt = $conn->prepare("SELECT APassword FROM admin WHERE AName = ?");
    $stmt->bind_param("s", $adminName);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verificar si la contraseña actual coincide
    if ($currentPassword !== $storedPassword) {
        echo "Invalid current password. Please try again.";
        return;
    }

    // Llamar al procedimiento almacenado para cambiar la contraseña
    $stmt = $conn->prepare("CALL changePasswordByName(?, ?)");
    $stmt->bind_param("ss", $adminName, $newPassword); // Los parámetros deben coincidir con el procedimiento

    // Ejecutar el procedimiento almacenado
    if ($stmt->execute()) {
        echo "Password updated successfully!";
    } else {
        echo "Error updating password: " . $conn->error;
    }

    // Cerrar el statement
    $stmt->close();
}




// Manejar cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Manejar filtro de agencias
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = $filter === 'unverified'
    ? "SELECT AgencyID, Name, Website FROM UnverifiedAgencies"
    : "SELECT AgencyID, Name, Website, agencyAuthenticated FROM agency" .
    ($filter === 'verified' ? " WHERE agencyAuthenticated = 1" : "");

$result = $conn->query($query);

// Procesar cambios de autenticación de agencias
if (isset($_GET['agency_id']) || isset($_GET['unverify_id'])) {
    $agency_id = isset($_GET['agency_id']) ? $_GET['agency_id'] : $_GET['unverify_id'];
    $status = isset($_GET['agency_id']) ? 1 : 0;

    $stmt = $conn->prepare("CALL changeAuthStatus(?, ?)");
    $stmt->bind_param('ii', $agency_id, $status);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF'] . "?filter=" . $filter);
    exit;
}

// Limpieza de resultados
while ($conn->next_result()) {
    $conn->store_result();
}

$agen = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="icon" href="/Classes/CS4342_5342 Dr. Villanueva/Team16 NVR/AdminCode/adminLogo.jpg" type="image/x-icon">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- Llamar al archivo CSS externo -->
    <link rel="stylesheet" href="styles.css">

</head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-building"></i>
                        Agencies Verification Dashboard
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="d-flex justify-content-between top-buttons">
                        <h5>Welcome to the Admin Dashboard</h5>
                        <div class="d-flex">
                            <button class="btn btn-info" data-toggle="modal" data-target="#profileModal">
                                <i class="fa fa-user"></i> Profile
                            </button>
                            <a href="?logout=true" class="btn btn-danger">
                                <i class="fa fa-sign-out"></i> Logout
                            </a>
                        </div>
                    </div>

                    <!-- Botones de filtro -->
                    <div class="filter-buttons">
                        <a href="?filter=all" class="btn btn-filter <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            All Agencies
                        </a>
                        <a href="?filter=verified" class="btn btn-filter <?php echo $filter === 'verified' ? 'active' : ''; ?>">
                            Verified Agencies
                        </a>
                        <a href="?filter=unverified" class="btn btn-filter <?php echo $filter === 'unverified' ? 'active' : ''; ?>">
                            Unverified Agencies
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Agency Name</th>
                                <th>Website</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($agen as $row): ?>
                                <tr>
                                    <td><?= $row['AgencyID'] ?></td>
                                    <td><?= htmlspecialchars($row['Name']) ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($row['Website']) ?>" target="_blank">
                                            <?= htmlspecialchars($row['Website']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?= isset($row['agencyAuthenticated']) && $row['agencyAuthenticated'] ? 'badge-verified' : 'badge-unverified' ?>">
                                            <?= isset($row['agencyAuthenticated']) ? ($row['agencyAuthenticated'] ? 'Verified' : 'Unverified') : 'Unverified' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($filter !== 'verified'): ?>
                                            <a href="?agency_id=<?= $row['AgencyID'] ?>&filter=<?= $filter ?>"
                                               class="btn btn-verify btn-sm"
                                               onclick="return confirm('Are you sure you want to verify this agency?')">
                                                Verify
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($filter !== 'unverified'): ?>
                                            <a href="?unverify_id=<?= $row['AgencyID'] ?>&filter=<?= $filter ?>"
                                               class="btn btn-unverify btn-sm"
                                               onclick="return confirm('Are you sure you want to unverify this agency?')">
                                                Unverify
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Modal -->
        <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="profileModalLabel">Admin Profile</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Name:</strong> <?= isset($_SESSION['AName']) ? htmlspecialchars($_SESSION['AName']) : 'N/A' ?></p>
                        <p><strong>Email:</strong> <?= isset($_SESSION['AEmail']) ? htmlspecialchars($_SESSION['AEmail']) : 'N/A' ?></p>
                        <p><strong>Password:</strong> ********</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#changeUsernameModal" data-dismiss="modal">
                            <i class="fa fa-user"></i> Change Username
                        </button>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#changeEmailModal" data-dismiss="modal">
                            <i class="fa fa-envelope"></i> Change Email
                        </button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#changePasswordModal" data-dismiss="modal">
                            <i class="fa fa-key"></i> Change Password
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

            <!-- Change Username Modal -->
            <div class="modal fade" id="changeUsernameModal" tabindex="-1" role="dialog" aria-labelledby="changeUsernameModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="changeUsernameModalLabel">Change Username</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                &times;
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
//                                AGREGAR CODIGO PHP DESPUES
                            ?>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="current_password_username">Current Password</label>
                                    <input type="password" class="form-control" id="current_password_username" name="current_password_username" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_username">New Username</label>
                                    <input type="text" class="form-control" id="new_username" name="new_username" required>
                                </div>
                                <button type="submit" name="change_username" class="btn btn-warning">
                                    <i class="fa fa-save"></i> Save New Username
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Email Modal -->
            <div class="modal fade" id="changeEmailModal" tabindex="-1" role="dialog" aria-labelledby="changeEmailModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="changeEmailModalLabel">Change Email</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                &times;
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php ?>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="current_password_email">Current Password</label>
                                    <input type="password" class="form-control" id="current_password_email" name="current_password_email" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_email">New Email</label>
                                    <input type="email" class="form-control" id="new_email" name="new_email" required>
                                </div>
                                <button type="submit" name="change_email" class="btn btn-info">
                                    <i class="fa fa-envelope"></i> Save New Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Modal -->
            <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="changePasswordModalLabel">Change Password</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                &times;
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
                            ?>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save New Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Bootstrap JS y dependencias -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        <script>
            // Optional: Add client-side validation for username and password change
            $(document).ready(function() {
                // Username change form validation
                $('#changeUsernameModal form').on('submit', function(e) {
                    var currentPassword = $('#current_password_username').val();
                    var newUsername = $('#new_username').val();

                    if (currentPassword.trim() === '' || newUsername.trim() === '') {
                        alert('Please fill in all fields');
                        e.preventDefault();
                        return false;
                    }

                    if (newUsername.length < 3) {
                        alert('Username must be at least 3 characters long');
                        e.preventDefault();
                        return false;
                    }
                });

                // Email change form validation
                $('#changeEmailModal form').on('submit', function(e) {
                    var currentPassword = $('#current_password_email').val();
                    var newEmail = $('#new_email').val();

                    if (currentPassword.trim() === '' || newEmail.trim() === '') {
                        alert('Please fill in all fields');
                        e.preventDefault();
                        return false;
                    }

                    // Basic email validation
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(newEmail)) {
                        alert('Please enter a valid email address');
                        e.preventDefault();
                        return false;
                    }
                });

                // Password change form validation
                $('#changePasswordModal form').on('submit', function(e) {
                    var currentPassword = $('#current_password').val();
                    var newPassword = $('#new_password').val();
                    var confirmPassword = $('#confirm_password').val();

                    if (currentPassword.trim() === '' || newPassword.trim() === '' || confirmPassword.trim() === '') {
                        alert('Please fill in all fields');
                        e.preventDefault();
                        return false;
                    }

                    if (newPassword.length < 8) {
                        alert('New password must be at least 8 characters long');
                        e.preventDefault();
                        return false;
                    }

                    if (newPassword !== confirmPassword) {
                        alert('New passwords do not match');
                        e.preventDefault();
                        return false;
                    }
                });
            });
        </script>
    </body>
</html>