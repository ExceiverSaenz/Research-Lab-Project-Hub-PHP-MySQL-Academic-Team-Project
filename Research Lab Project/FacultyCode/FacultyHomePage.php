<?php
session_start();

// Check if the user is logged in and is a faculty member
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: ../index.php"); // Redirect to login if not logged in
    exit();
}

// Display the faculty's username (e.g., email) if stored in session
$username = isset($_SESSION['user']) ? $_SESSION['user'] : "Faculty Member";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Home Page</title>
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

        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 50px;
        }

        .button-card {
            text-align: center;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s ease-in-out;
            width: 200px;
        }

        .button-card:hover {
            transform: scale(1.05);
        }

        .button-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            cursor: pointer;
        }

        .button-card button {
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .button-card button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<!-- Header -->
<div class="header">
    <!-- Left: Faculty Username -->
    <span><?php echo htmlspecialchars($username); ?></span>

    <!-- Middle: Building Image -->
    <img src="../building3.png" alt="Building">

    <!-- Right: Logout Button -->
    <form action="/Classes/CS4342_5342 Dr. Villanueva/Team16 NVR/Logout.php" method="post" style="display: inline;">
        <button type="submit" class="logout-button">Log Out</button>
    </form>
</div>

<!-- Button Grid -->
<div class="container">
    <div class="button-container">
        <!-- Student Authentication -->
        <div class="button-card">
            <a href="Authentication.php">
                <img src="Pictures/Authenticate Student.png" alt="Authenticate Student">
            </a>
            <button onclick="location.href='Authentication.php'">Authenticate Students</button>
        </div>

        <!-- Supervise Your Students -->
        <div class="button-card">
            <a href="Faculty_SSupervise.php">
                <img src="Pictures/Supervise Students.png" alt="Supervise Students">
            </a>
            <button onclick="location.href='Faculty_SSupervise.php'">Supervise Your Students</button>
        </div>

        <!-- View Projects -->
        <div class="button-card">
            <a href="Projects_Page.php">
                <img src="Pictures/Projects.png" alt="View Projects">
            </a>
            <button onclick="location.href='Projects_Page.php'">View Projects</button>
        </div>

        <!-- Supervise Your Projects -->
        <div class="button-card">
            <a href="Faculty_PSupervise.php">
                <img src="Pictures/Supervising Projects.png" alt="Supervise Projects">
            </a>
            <button onclick="location.href='Faculty_PSupervise.php'">Supervise Your Projects</button>
        </div>
    </div>
</div>
</body>
</html>
