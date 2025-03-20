<?php
session_start();
require_once("config.php");

if (isset($_POST['Submit'])) {
    $account_type = isset($_POST['account_type']) ? $_POST['account_type'] : '';
    $input_username = isset($_POST['username']) ? $_POST['username'] : '';
    $input_password = isset($_POST['password']) ? $_POST['password'] : '';

    // Define query and variables based on account type
    if ($account_type === "student") {
        $query = "SELECT * FROM Students WHERE SFName = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($input_password, $row['Spassword'])) {
                $_SESSION['user'] = $input_username;
                $_SESSION['logged_in'] = true;
                $_SESSION['StudentID'] = $row['StudentID'];
                header("Location: ./studentsCode/student_menu.php");
                exit();
            } else {
                // Password verification failed
                $error_message = "Incorrect password."; 
            }
        }
    } 
    elseif ($account_type === "faculty") {
        $query = "SELECT * FROM Faculty WHERE FName = ?";
        $redirect_url = "FacultyCode/FacultyHomePage.php";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
//            echo $input_username . " " . $input_password . " " . $row['Fpassword'];
            if ($input_password == $row['Fpassword']) {
                echo $input_username . " " . $input_password . " " . $row['Fpassword'];
                $_SESSION['user'] = $input_username;
                $_SESSION['logged_in'] = true;
                $_SESSION['FID'] = $row['FID'];
                $_SESSION['FEmail'] = $row['FEmail'];
                header("Location: " . $redirect_url);
                exit();
            }
        }
    } 
    elseif ($account_type === "agency") {
        $query = "SELECT AgencyID, Name FROM agency WHERE Name = ? AND Apassword = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $input_username, $input_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $agency = $result->fetch_assoc();
            $_SESSION['user_type'] = 'agency';
            $_SESSION['agency_id'] = $agency['AgencyID'];
            $_SESSION['agency_name'] = $agency['Name'];
            header("Location: ./AgencyCode/agencyprojects.php");
            exit();
        }
    }
    elseif ($account_type === "admin") {
        $query = "SELECT * FROM Admin WHERE AEmail = ? AND APassword = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $input_username, $input_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $_SESSION['user_type'] = 'admin';
            $_SESSION['admin_id'] = $admin['AdminID'];
            $_SESSION['admin_name'] = $admin['AName'];
            header("Location: AdminCode/adminDashboard.php");
            exit();
        }
    }

    $error_message = "Invalid credentials. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Research Lab Project Hub - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 500px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .login-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: 500;
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header text-center">
                <i class="fas fa-flask login-icon"></i>
                <h3 class="mb-0">Research Lab Project Hub</h3>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Select Account Type</label>
                        <div class="d-flex justify-content-between gap-2">
<!--                            <div class="form-check flex-grow-1">-->
<!--                                <input class="form-check-input" type="radio" id="student" -->
<!--                                       name="account_type" value="student" onclick="updateFields()" checked>-->
<!--                                <label class="form-check-label" for="student">-->
<!--                                    <i class="fas fa-user-graduate me-2"></i>Student-->
<!--                                </label>-->
<!--                            </div>-->
                            <div class="form-check flex-grow-1">
                                <input class="form-check-input" type="radio" id="faculty" 
                                       name="account_type" value="faculty" onclick="updateFields()">
                                <label class="form-check-label" for="faculty">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>Faculty
                                </label>
                            </div>
                            <div class="form-check flex-grow-1">
                                <input class="form-check-input" type="radio" id="agency" 
                                       name="account_type" value="agency" onclick="updateFields()">
                                <label class="form-check-label" for="agency">
                                    <i class="fas fa-building me-2"></i>Agency
                                </label>
                            </div>
<!--                            <div class="form-check flex-grow-1">-->
<!--                                <input class="form-check-input" type="radio" id="admin" -->
<!--                                       name="account_type" value="admin" onclick="updateFields()">-->
<!--                                <label class="form-check-label" for="admin">-->
<!--                                    <i class="fas fa-user-cog me-2"></i>Admin-->
<!--                                </label>-->
<!--                            </div>-->
                        </div>
                    </div>

                    <div class="mb-3">
                        <label id="username-label" class="form-label">
                            <i class="fas fa-user me-2"></i>
                            <span id="username-text">First Name</span>
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" name="Submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>

                    <div class="text-center">
                        <a href="createUser.php" class="text-decoration-none">
                            Don't have an account? Create one now!
                        </a>
                    </div>
                    <div class="text-center">
                        <a href="AdminCode/Adminlogin.php" class="text-decoration-none">
                            Login Admin
                        </a>
                    </div>
                    <div class="text-center">
                        <a href="studentsCode/student_login.php" class="text-decoration-none">
                            Student Login
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateFields() {
            const accountType = document.querySelector('input[name="account_type"]:checked').value;
            const usernameLabel = document.getElementById("username-text");
            const usernameInput = document.getElementById("username");

            if (accountType === "student") {
                usernameLabel.innerText = "First Name";
                usernameInput.placeholder = "Enter your first name";
            } 
//else if (