<?php
session_start();
require_once("../config.php");

// Check if the user is logged in and is a faculty member
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: ../index.php"); // Redirect to login if not logged in
    exit();
}

$facultyID = $_SESSION['FID']; // Get the logged-in faculty's ID

// Handle the Join button logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_project'])) {
    $projectID = $_POST['project_id'];


    // Prevent duplicate entries in Faculty_Participate
    $check_query = "SELECT * FROM Faculty_Participate WHERE FID = ? AND PID = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $facultyID, $projectID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color: red;'>You are already participating in this project.</p>";
    } else {

        // Insert into Faculty_Participate
        $insert_query = "INSERT INTO Faculty_Participate (FID, PID) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $facultyID, $projectID);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Successfully joined the project!</p>";
        } else {
            echo "<p style='color: red;'>Failed to join the project. Please try again.</p>";
        }
    }
}

// Fetch all projects from the Project table
$query = "SELECT * FROM Project";
$result = $conn->query($query);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Page</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .button {
            padding: 5px 10px;
            background-color: blue;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .button:hover {
            background-color: darkblue;
        }
    </style>
</head>
<body>
<h1>Projects Page</h1>
<!-- Home button -->
<form action="FacultyHomePage.php" method="get" style="margin-top: 20px;">
    <button type="submit" class="btn btn-primary">Home</button>
</form>
<table>
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
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['PID']); ?></td>
                <td><?php echo htmlspecialchars($row['PName']); ?></td>
                <td><?php echo htmlspecialchars($row['PObjective']); ?></td>
                <td><?php echo htmlspecialchars($row['PStartDate']); ?></td>
                <td><?php echo htmlspecialchars($row['PEndDate']); ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="project_id" value="<?php echo $row['PID']; ?>">
                        <button type="submit" name="join_project" class="button">Join</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">No projects available.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>
