<?php
session_start();
require_once '../config.php';

$message = '';
$tasks = [];

// Get all tasks
try {
    $sql = "SELECT * FROM task ORDER BY TaskStatus ASC, TName ASC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
} catch (Exception $e) {
    $message = "Error fetching tasks: " . $e->getMessage();
}

// Handle task deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $taskID = $_POST['delete_task'];
    
    try {
        $sql = "DELETE FROM task WHERE TaskID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $taskID);
        
        if ($stmt->execute()) {
            $message = "Task deleted successfully!";
            header("Location: delete_task.php");
            exit();
        } else {
            $message = "Error deleting task: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Delete Tasks</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($tasks)): ?>
            <div class="alert alert-info">No tasks found.</div>
        <?php else: ?>
            <div class="table-responsive mt-4">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['TaskID']); ?></td>
                                <td><?php echo htmlspecialchars($task['TName']); ?></td>
                                <td><?php echo htmlspecialchars($task['TDescription']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $task['TaskStatus'] === 'Completed' ? 'success' : 
                                            ($task['TaskStatus'] === 'In Progress' ? 'warning' : 'secondary');
                                    ?>">
                                        <?php echo htmlspecialchars($task['TaskStatus']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="delete_task" value="<?php echo $task['TaskID']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to delete this task?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a href="student_menu.php" class="btn btn-secondary mt-3">Back to Menu</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>