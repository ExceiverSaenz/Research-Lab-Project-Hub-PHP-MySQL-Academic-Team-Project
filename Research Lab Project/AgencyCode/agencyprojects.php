<?php
session_start();
require_once("../config.php");

$agency_id = $_SESSION['agency_id'];

// Get projects for this agency
$query = "SELECT p.*, 
         GROUP_CONCAT(pd.PDeliverable SEPARATOR ', ') as deliverables
         FROM project p
         LEFT JOIN Manage m ON p.PID = m.PID
         LEFT JOIN p_deliverables pd ON p.PID = pd.PID
         WHERE m.AgencyID = ?
         GROUP BY p.PID";

$projects = array();
if ($stmt = $conn->prepare($query)) {
   $stmt->bind_param("i", $agency_id);
   $stmt->execute();
   $result = $stmt->get_result();
   while($row = $result->fetch_assoc()) {
       $projects[] = $row;
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Projects for <?php echo $_SESSION['agency_name']; ?></title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
   <div class="navbar navbar-dark bg-primary p-3">
       <div class="d-flex justify-content-between w-100 align-items-center">
           <div class="d-flex align-items-center">
               <i class="fas fa-project-diagram text-white me-2"></i>
               <h3 class="text-white mb-0">Projects for <?php echo $_SESSION['agency_name']; ?></h3>
           </div>
           <div>
               <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#reportModal">
                   <i class="fas fa-file-alt me-2"></i>Generate Report
               </button>
               <a href="add_project.php" class="btn btn-light me-2">
                   <i class="fas fa-plus me-2"></i>Create New Project
               </a>
               <a href="logout.php" class="btn btn-light">
                   <i class="fas fa-sign-out-alt me-2"></i>Logout
               </a>
           </div>
       </div>
   </div>

   <!-- Report Modal -->
   <div class="modal fade" id="reportModal">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Generate Report</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <div class="d-grid gap-2">
                       <a href="?report=projects" class="btn btn-primary">Project Details Report</a>
                       <a href="?report=status" class="btn btn-primary">Project Status Report</a>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <!-- Project Table -->
   <div class="container-fluid mt-4">
       <div class="table-responsive">
           <table class="table table-hover">
               <thead>
                   <tr>
                       <th>Project ID</th>
                       <th>Name</th>
                       <th>Objective</th>
                       <th>Start Date</th>
                       <th>End Date</th>
                       <th>Actions</th>
                   </tr>
               </thead>
               <tbody>
                   <?php foreach ($projects as $project): ?>
                       <tr>
                           <td><?php echo $project['PID']; ?></td>
                           <td><?php echo $project['PName']; ?></td>
                           <td><?php echo $project['PObjective']; ?></td>
                           <td><?php echo $project['PStartDate']; ?></td>
                           <td><?php echo $project['PEndDate']; ?></td>
                           <td>
                               <div class="btn-group">
                                   <a href="edit_project.php?id=<?php echo $project['PID']; ?>" 
                                      class="btn btn-warning btn-sm">
                                       <i class="fas fa-edit"></i>
                                   </a>
                                   <a href="delete_project.php?id=<?php echo $project['PID']; ?>" 
                                      class="btn btn-danger btn-sm"
                                      onclick="return confirm('Are you sure you want to delete this project?')">
                                       <i class="fas fa-trash"></i>
                                   </a>
                               </div>
                           </td>
                       </tr>
                   <?php endforeach; ?>
               </tbody>
           </table>
       </div>
   </div>

   <!-- Report Display -->
   <?php if (isset($_GET['report']) && $_GET['report'] === 'projects'): ?>
       <div class="card mt-4">
           <div class="card-header bg-primary text-white">
               <h3 class="mb-0">Projects Report</h3>
           </div>
           <div class="card-body">
               <!-- Project Selection Form -->
               <form method="GET" class="mb-4">
                   <input type="hidden" name="report" value="projects">
                   <div class="row align-items-end">
                       <div class="col-md-4">
                           <label class="form-label">Select Project:</label>
                           <select name="project_id" class="form-control" onchange="this.form.submit()">
                               <option value="">Select a project...</option>
                               <?php foreach ($projects as $proj): ?>
                                   <option value="<?php echo $proj['PID']; ?>" 
                                       <?php echo (isset($_GET['project_id']) && $_GET['project_id'] == $proj['PID']) ? 'selected' : ''; ?>>
                                       <?php echo htmlspecialchars($proj['PName']); ?>
                                   </option>
                               <?php endforeach; ?>
                           </select>
                       </div>
                   </div>
               </form>

               <?php
               if (isset($_GET['project_id'])) {
                   $report_sql = "SELECT p.*, 
                                GROUP_CONCAT(CONCAT(pd.PDeliverable, ' (Due: ', pd.PDeadline, ')') SEPARATOR '\n') as deliverables
                                FROM project p
                                LEFT JOIN p_deliverables pd ON p.PID = pd.PID
                                WHERE p.PID = ? AND EXISTS (
                                    SELECT 1 FROM Manage m WHERE m.PID = p.PID AND m.AgencyID = ?
                                )
                                GROUP BY p.PID";
                   
                   $stmt = $conn->prepare($report_sql);
                   $stmt->bind_param("ii", $_GET['project_id'], $_SESSION['agency_id']);
                   $stmt->execute();
                   $project_details = $stmt->get_result()->fetch_assoc();

                   if ($project_details): ?>
                       <table class="table table-bordered">
                           <tr>
                               <th>Project ID</th>
                               <td><?php echo $project_details['PID']; ?></td>
                           </tr>
                           <tr>
                               <th>Name</th>
                               <td><?php echo htmlspecialchars($project_details['PName']); ?></td>
                           </tr>
                           <tr>
                               <th>Objective</th>
                               <td><?php echo htmlspecialchars($project_details['PObjective']); ?></td>
                           </tr>
                           <tr>
                               <th>Start Date</th>
                               <td><?php echo $project_details['PStartDate']; ?></td>
                           </tr>
                           <tr>
                               <th>End Date</th>
                               <td><?php echo $project_details['PEndDate']; ?></td>
                           </tr>
                           <tr>
                               <th>Deliverables</th>
                               <td>
                                   <?php
                                   if ($project_details['deliverables']) {
                                       $deliverables = explode("\n", $project_details['deliverables']);
                                       echo "<ul class='mb-0'>";
                                       foreach ($deliverables as $deliverable) {
                                           echo "<li>" . htmlspecialchars($deliverable) . "</li>";
                                       }
                                       echo "</ul>";
                                   } else {
                                       echo "No deliverables found";
                                   }
                                   ?>
                               </td>
                           </tr>
                       </table>
                   <?php else: ?>
                       <div class="alert alert-info">No project details found.</div>
                   <?php endif;
               }
               ?>
           </div>
       </div>
   <?php endif; ?>

<!-- Project Status Report -->
<?php if (isset($_GET['report']) && $_GET['report'] === 'status'): ?>
    <div class="card mt-4">
        <div class="card-header bg-primary text-pink">
            <h3 class="mb-0">Project Status Report</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Name</th>
                        <th>Days Remaining</th>
                        <th>Completion Status</th>
                        <th>Deliverables Count</th>
                        <th>Timeline</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $status_sql = "SELECT 
                        p.PID,
                        p.PName,
                        p.PStartDate,
                        p.PEndDate,
                        DATEDIFF(p.PEndDate, CURDATE()) as days_remaining,
                        COUNT(pd.PDeliverable) as deliverables_count
                        FROM project p
                        LEFT JOIN p_deliverables pd ON p.PID = pd.PID
                        JOIN Manage m ON p.PID = m.PID
                        WHERE m.AgencyID = ?
                        GROUP BY p.PID";
                    
                    $stmt = $conn->prepare($status_sql);
                    $stmt->bind_param("i", $_SESSION['agency_id']);
                    $stmt->execute();
                    $status_result = $stmt->get_result();

                    while ($project = $status_result->fetch_assoc()):
                        $days = $project['days_remaining'];
                        $status_class = $days < 0 ? 'text-danger' : ($days < 30 ? 'text-warning' : 'text-success');
                        $status_text = $days < 0 ? 'Overdue' : ($days < 30 ? 'Due Soon' : 'On Track');
                    ?>
                        <tr>
                            <td><?php echo $project['PID']; ?></td>
                            <td><?php echo htmlspecialchars($project['PName']); ?></td>
                            <td class="<?php echo $status_class; ?>">
                                <?php echo $days > 0 ? $days . ' days left' : abs($days) . ' days overdue'; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $days < 0 ? 'danger' : ($days < 30 ? 'warning' : 'success'); ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td><?php echo $project['deliverables_count']; ?></td>
                            <td>
                                <?php 
                                echo date('M d, Y', strtotime($project['PStartDate'])) . ' - ' . 
                                     date('M d, Y', strtotime($project['PEndDate'])); 
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
