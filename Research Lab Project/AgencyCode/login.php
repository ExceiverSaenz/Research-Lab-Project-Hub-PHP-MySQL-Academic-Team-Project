<?php
session_start();
require_once("../config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // This will be the AgencyID
    $password = $_POST['password']; // This will be the Apassword
    
    // Check if it's an agency login
    $query = "SELECT AgencyID, Name FROM agency WHERE AgencyID = ? AND Apassword = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $agency = $result->fetch_assoc();
            $_SESSION['user_type'] = 'agency';
            $_SESSION['agency_id'] = $agency['AgencyID'];
            $_SESSION['agency_name'] = $agency['Name'];
            header("Location: agencyprojects.php");
            exit();
        } else {
            // Check admin login
            if ($username === "admin" && $password === "admin1") {
                $_SESSION['user_type'] = 'admin';
                header("Location: agency_dashboard.php");
                exit();
            } else {
                $error_message = "Invalid credentials";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Login</title>
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
        .login-btn {
            padding: 10px 20px;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header text-center">
                <i class="fas fa-building login-icon"></i>
                <h3 class="mb-0">Agency Login</h3>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Agency ID or Admin Username
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               required 
                               placeholder="Enter your Agency ID or admin username">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Website URL or Admin Password
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required
                               placeholder="Enter your website URL or admin password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 login-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                <div class="mt-3 text-center text-muted">
                    <small>
                        Agencies: Use your Agency ID and Website URL<br>
                        Admin: Use admin credentials
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>