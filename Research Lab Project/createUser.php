<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>CS4342 Create Account</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>

<body>
<div style="margin-top: 20px" class="container">
    <h1>Create Account</h1>

    <form id="accountForm" action="createUser.php" method="post" onsubmit="return validateForm()">
        <div class="form-group">
            <label>Account Type</label>
            <div>
                <input type="radio" id="student" name="account_type" value="student" onclick="showFields()" checked>
                <label for="student">Student</label>
                <input type="radio" id="faculty" name="account_type" value="faculty" onclick="showFields()">
                <label for="faculty">Faculty</label>
                <input type="radio" id="agency" name="account_type" value="agency" onclick="showFields()">
                <label for="agency">Agency</label>
<!--                <input type="radio" id="admin" name="account_type" value="admin" onclick="showFields()">-->
<!--                <label for="admin">Admin</label>-->
            </div>
        </div>

        <!-- Student-specific fields -->
        <div id="studentFields">
            <div class="form-group">
                <label for="SFName">First Name</label>
                <input class="form-control" type="text" id="SFName" name="SFName" required>
            </div>
            <div class="form-group">
                <label for="SMName">Middle Name</label>
                <input class="form-control" type="text" id="SMName" name="SMName">
            </div>
            <div class="form-group">
                <label for="SLName">Last Name</label>
                <input class="form-control" type="text" id="SLName" name="SLName" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="SDepartment">Department</label>
                <select class="form-control" id="SDepartment" name="SDepartment" required>
                    <option value="">Select Department</option>
                    <!-- Department options remain the same as in the original code -->
                    <option value="Computer Science">Computer Science</option>
                    <!-- ... other departments ... -->
                </select>
            </div>
            <div class="form-group">
                <label for="level">Level of Education</label>
                <select class="form-control" id="level" name="level" required>
                    <option value="Graduate">Graduate</option>
                    <option value="Undergraduate">Undergraduate</option>
                </select>
            </div>
        </div>

        <!-- Faculty-specific fields -->
        <div id="facultyFields" style="display: none;">
            <div class="form-group">
                <label for="femail">Email</label>
                <input class="form-control" type="email" id="femail" name="femail">
            </div>
            <div class="form-group">
                <label for="fname">Name</label>
                <input class="form-control" type="text" id="fname" name="fname">
            </div>
            <div class="form-group">
                <label for="ftitle">Title</label>
                <input class="form-control" type="text" id="ftitle" name="ftitle">
            </div>
            <div class="form-group">
                <label for="fdepartment">Department</label>
                <input class="form-control" type="text" id="fdepartment" name="fdepartment">
            </div>
        </div>

        <!-- Agency specific fields -->
        <div id="agencyFields" style="display: none;">
            <div class="form-group">
                <label for="aname">Name</label>
                <input class="form-control" type="text" id="aname" name="aname">
            </div>
            <div class="form-group">
                <label for="awebsite">Website</label>
                <input class="form-control" type="url" id="awebsite" name="awebsite"
                       placeholder="https://www.example.com">
            </div>
        </div>

        <!-- Admin-specific fields -->
        <div id="adminFields" style="display: none;">
            <div class="form-group">
                <label for="adname">Name</label>
                <input class="form-control" type="text" id="adname" name="adname">
            </div>
            <div class="form-group">
                <label for="ademail">Email</label>
                <input class="form-control" type="email" id="ademail" name="ademail">
            </div>
        </div>

<div class="form-group">
    <label for="password">Password</label>
    <input class="form-control" type="password" id="password" name="password" required
           minlength="4"
           title="The password must be at least 4 characters long.">
    <small class="form-text text-muted">
        The password must be at least 4 characters long.
    </small>
</div>

        <div class="form-group d-flex justify-content-center">
            <button class="btn btn-primary" name="Submit" type="submit">Create Account</button>
        </div>
    </form>

    <div>
        <br>
        <a href="index.php">Go Back Login</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function showFields() {
        const accountType = document.querySelector('input[name="account_type"]:checked').value;

        // Ocultar todos los campos específicos
        const fieldContainers = {
            'student': document.getElementById('studentFields'),
            'faculty': document.getElementById('facultyFields'),
            'agency': document.getElementById('agencyFields'),
            'admin': document.getElementById('adminFields')
        };

        // Ocultar todos los campos
        Object.values(fieldContainers).forEach(container => {
            container.style.display = 'none';
        });

        // Mostrar solo los campos del tipo de cuenta seleccionado
        fieldContainers[accountType].style.display = 'block';

        // Deshabilitar/habilitar y hacer requeridos los campos apropiados
        Object.entries(fieldContainers).forEach(([type, container]) => {
            const inputs = container.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (type === accountType) {
                    input.disabled = false;
                    if (input.dataset.required) {
                        input.setAttribute('required', '');
                    }
                } else {
                    input.disabled = true;
                    input.removeAttribute('required');
                }
            });
        });
    }

    function validateForm() {
        const accountType = document.querySelector('input[name="account_type"]:checked').value;
        let isValid = true;

        // Validaciones específicas por tipo de cuenta
        switch(accountType) {
            case 'student':
                isValid = validateStudentFields();
                break;
            case 'faculty':
                isValid = validateFacultyFields();
                break;
            case 'agency':
                isValid = validateAgencyFields();
                break;
            case 'admin':
                isValid = validateAdminFields();
                break;
        }

        // Validación de contraseña
        const password = document.getElementById('password');
        if (!password.checkValidity()) {
            password.reportValidity();
            return false;
        }

        return isValid;
    }

    function validateStudentFields() {
        const requiredFields = [
            'SFName', 'SLName', 'email', 'SDepartment', 'level'
        ];
        return checkRequiredFields(requiredFields);
    }

    function validateFacultyFields() {
        const requiredFields = [
            'femail', 'fname', 'fdepartment'
        ];
        return checkRequiredFields(requiredFields);
    }

    function validateAgencyFields() {
        const requiredFields = [
            'aname'
        ];
        return checkRequiredFields(requiredFields);
    }

    function validateAdminFields() {
        const requiredFields = [
            'adname', 'ademail'
        ];
        return checkRequiredFields(requiredFields);
    }

    function checkRequiredFields(fieldIds) {
        for (let fieldId of fieldIds) {
            const field = document.getElementById(fieldId);
            if (!field || field.disabled) continue;

            if (!field.value.trim()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    }

    // Ejecutar showFields al cargar la página
    document.addEventListener('DOMContentLoaded', showFields);
</script>

<?php
require_once('config.php');

if (isset($_POST['Submit'])) {
    // Función para sanitizar entradas
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    $password = isset($_POST['password']) ? $_POST['password'] : "";
    $hashed_password = $password;
//    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $account_type = isset($_POST['account_type']) ? sanitizeInput($_POST['account_type']) : "";

    // Preparar declaración para prevenir inyección SQL
    switch ($account_type) {
        case "student":
            $name = sanitizeInput(isset($_POST['SFName']) ? $_POST['SFName'] : "");
            $middle_name = sanitizeInput(isset($_POST['SMName']) ? $_POST['SMName'] : "");
            $last_name = sanitizeInput(isset($_POST['SLName']) ? $_POST['SLName'] : "");
            $email = sanitizeInput(isset($_POST['email']) ? $_POST['email'] : "");
            $department = sanitizeInput(isset($_POST['SDepartment']) ? $_POST['SDepartment'] : "");
            $level = sanitizeInput(isset($_POST['level']) ? $_POST['level'] : "");

            $StudentID = mt_rand(1, 500);
            $stmt = $conn->prepare("INSERT INTO Student (StudentID, SFName, SMName, SLName, SEmail, SDepartment, SClassification_type, Spassword) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $StudentID, $name, $middle_name, $last_name, $email, $department, $level, $hashed_password);
            break;

        case "faculty":
            $femail = sanitizeInput(isset($_POST['femail']) ? $_POST['femail'] : "");
            $fname = sanitizeInput(isset($_POST['fname']) ? $_POST['fname'] : "");
            $ftitle = sanitizeInput(isset($_POST['ftitle']) ? $_POST['ftitle'] : "");
            $fdepartment = sanitizeInput(isset($_POST['fdepartment']) ? $_POST['fdepartment'] : "");

            $fid = mt_rand(1, 500);
            $stmt = $conn->prepare("INSERT INTO Faculty (FID, FEmail, FName, FTitle, FDepartment, Fpassword) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $fid, $femail, $fname, $ftitle, $fdepartment, $hashed_password);
            break;

        case "agency":
            $aname = sanitizeInput(isset($_POST['aname']) ? $_POST['aname'] : "");
            $awebsite = sanitizeInput(isset($_POST['awebsite']) ? $_POST['awebsite'] : "");

            $aid = mt_rand(1, 500);
            $stmt = $conn->prepare("INSERT INTO Agency (AgencyID, Name, Website, Apassword) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $aid, $aname, $awebsite, $hashed_password);
            break;

        case "admin":
            $adname = sanitizeInput(isset($_POST['adname']) ? $_POST['adname'] : "");
            $ademail = sanitizeInput(isset($_POST['ademail']) ? $_POST['ademail'] : "");

            $aid = mt_rand(1, 500);
            $stmt = $conn->prepare("INSERT INTO Admin (AdminID, AName, AEmail, Apassword) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $aid, $adname, $ademail, $hashed_password);
            break;

        default:
            echo "Tipo de cuenta no válido";
            exit;
    }

    // Ejecutar la declaración preparada
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Usuario creado exitosamente!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

</body>
</html>