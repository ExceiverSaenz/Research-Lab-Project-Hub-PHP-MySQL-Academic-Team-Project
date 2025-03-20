<?php
session_start();
require_once("../config.php");

$StudentID = $_SESSION['StudentID'];

$query = " SELECT * FROM studentProject_view WHERE StudentID = ?";

$studentTasks = array();

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $StudentID);
    $stmt->execute();
    $result = $stmt->get_result();


    while($row = $result->fetch_assoc()) {
        $studentTasks[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Projects for <?php echo htmlspecialchars($_SESSION['SFName']); ?></title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
   <div class="navbar navbar-dark bg-primary p-3">
       <div class="d-flex justify-content-between w-100 align-items-center">
           <div class="d-flex align-items-center">
               <i class="fas fa-project-diagram text-white me-2"></i>
               <h3 class="text-white mb-0">Projects for <?php echo $_SESSION['SFName']; ?></h3>
           </div>
           <div>
           

               <a href="student_menu.php" class="btn btn-light">
                   <i class="fas fa-sign-out-alt me-2"></i>Back
               </a>
           </div>
       </div>
   </div>


   <!-- Tasks Table -->
   <div class="container-fluid mt-4">
       <div class="table-responsive">
           <table class="table table-hover">
               <thead>
                   <tr>
                       <th>Project ID</th>
                       <th>Project Name</th>
                       <th>Task ID</th>
                       <th>Task Name</th>
                       <th>Start Date</th>
                       <th>End Date</th>
                       <th>Task Status</th>
                   </tr>
               </thead>
               <tbody>
                   <?php foreach ($studentTasks as $studentTask): ?>
                       <tr>
                           <td><?php echo $studentTask['PID']; ?></td>
                           <td><?php echo $studentTask['PName']; ?></td>
                           <td><?php echo $studentTask['TaskID']; ?></td>
                           <td><?php echo $studentTask['TName']; ?></td>
                           <td><?php echo $studentTask['PStartDate']; ?></td>
                           <td><?php echo $studentTask['PEndDate']; ?></td>
                           <td><?php echo $studentTask['TaskStatus']; ?></td>
                           <td>
                                <form action="update_task_status.php" method="post"> 
                                    <input type="hidden" name="task_id" value="<?php echo $task['TaskID']; ?>">
                                    <select name="task_status">
                                        <option value="Not Started" <?php if ($studentTask['TaskStatus'] == 'Not Started') echo 'selected'; ?>>Not Started</option>
                                        <option value="In Progress" <?php if ($studentTask['TaskStatus'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                        <option value="Completed" <?php if ($studentTask['TaskStatus'] == 'Completed') echo 'selected'; ?>>Completed</option>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                           </td>
                       </tr>
                   <?php endforeach; ?>
               </tbody>
           </table>
       </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
