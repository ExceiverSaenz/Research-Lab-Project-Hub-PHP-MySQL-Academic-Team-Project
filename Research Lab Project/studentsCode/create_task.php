<?php
session_start();
require_once '../config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $taskName = trim($_POST['taskName']);
    $description = trim($_POST['description']);
    
    if (empty($taskName)) {
        $message = "Task name is required";
    } else {
        try {
            // Generate a unique TaskID
            $taskID = uniqid('TASK_');
            
            // Insert the new task
            $sql = "INSERT INTO task (TaskID, TName, TDescription, TaskStatus) VALUES (?, ?, ?, 'Not Started')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $taskID, $taskName, $description);
            
            if ($stmt->execute()) {
                $message = "Task created successfully!";
            } else {
                $message = "Error creating task: " . $conn->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Create New Task</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="mt-4">
            <div class="mb-3">
                <label for="taskName" class="form-label">Task Name</label>
                <input type="text" class="form-control" id="taskName" name="taskName" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Create Task</button>
                <a href="student_menu.php" class="btn btn-secondary">Back to Menu</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>