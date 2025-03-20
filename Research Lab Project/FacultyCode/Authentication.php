<?php
session_start();
require_once("../config.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in as a faculty member
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['FID'])) {
    header("Location: ../index.php");
    exit();
}

$facultyID = $_SESSION['FID']; // Get the logged-in faculty's ID
$message = "";

// Handle actions (Authenticate, Deny, Assign Supervision)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : null;
    $action = isset($_POST['action']) ? $_POST['action'] : null;

    if ($student_id && $action) {
        if ($action === 'authenticate' || $action === 'deny') {
            // Update authentication status
            $authenticated = ($action === 'authenticate') ? 1 : 0;
            $stmt = $conn->prepare("UPDATE Student SET authenticated = ? WHERE StudentID = ?");
            $stmt->bind_param("ii", $authenticated, $student_id);

            if ($stmt->execute()) {
                $message = "Student " . ($authenticated ? "authenticated" : "denied") . " successfully.";
            } else {
                $message = "Error updating student authentication: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action === 'assign_supervision') {
            // Assign the student to the faculty in Faculty_SSupervise
            $supervisor_type = isset($_POST['supervisor_type']) ? $_POST['supervisor_type'] : 'Advisor';
            $stmt = $conn->prepare("INSERT INTO Faculty_SSupervise (FID, StudentID, SupervisorType) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $facultyID, $student_id, $supervisor_type);

            if ($stmt->execute()) {
                $message = "Student assigned as '$supervisor_type' successfully.";
            } else {
                $message = "Error assigning student: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid student ID or action.";
    }
}

// Fetch all students from the database
$students = [];
$result = $conn->query("SELECT StudentID, SFName, SMName, SLName, SEmail, SDepartment, SClassification_type, authenticated FROM Student");

if ($result) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error fetching students: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authenticate Students</title>
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
    <span><?php echo htmlspecialchars($username); ?></span>
    <img src="../building3.png" alt="Building">
    <form action="/Classes/CS4342_5342 Dr. Villanueva/Team16 NVR/FacultyCode/FacultyHomePage.php" method="post" style="display: inline;">
        <button type="submit" class="logout-button">Home</button>
    </form>
</div>

<!-- Main Content -->
<div class="container mt-4">
    <h1>Student Authentication</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Student ID</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Classification</th>
            <th>Authenticated</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['StudentID']) ?></td>
                <td><?= htmlspecialchars($student['SFName']) ?></td>
                <td><?= htmlspecialchars($student['SMName'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($student['SLName']) ?></td>
                <td><?= htmlspecialchars($student['SEmail']) ?></td>
                <td><?= htmlspecialchars($student['SDepartment']) ?></td>
                <td><?= htmlspecialchars($student['SClassification_type']) ?></td>
                <td><?= $student['authenticated'] ? 'Yes' : 'No' ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="student_id" value="<?= $student['StudentID'] ?>">
                        <button type="submit" name="action" value="authenticate" class="btn btn-success btn-sm">Authenticate</button>
                    </form>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="student_id" value="<?= $student['StudentID'] ?>">
                        <button type="submit" name="action" value="deny" class="btn btn-danger btn-sm">Deny</button>
                    </form>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="student_id" value="<?= $student['StudentID'] ?>">
                        <select name="supervisor_type" class="form-control-sm">
                            <option value="Advisor">Advisor</option>
                            <option value="Mentor">Mentor</option>
                            <option value="Thesis/Dissertation Advisor">Thesis/Dissertation Advisor</option>
                            <option value="Committee Member">Committee Member</option>
                            <option value="Research Supervisor">Research Supervisor</option>
                            <option value="Independent Study Supervisor">Independent Study Supervisor</option>
                            <option value="Other">Other</option>
                        </select>
                        <button type="submit" name="action" value="assign_supervision" class="btn btn-info btn-sm">Assign Supervision</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
