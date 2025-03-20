<?php
session_start();
require_once("../config.php");

// Check if user is logged in as agency
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agency') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $agency_id = $_SESSION['agency_id'];

    try {
        $conn->begin_transaction();

        // First delete from p_deliverables
        $sql = "DELETE FROM p_deliverables WHERE PID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();

        // Delete from Manage table
        $sql = "DELETE FROM Manage WHERE PID = ? AND AgencyID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $project_id, $agency_id);
        $stmt->execute();

        // Finally delete the project
        $sql = "DELETE FROM project WHERE PID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();

        $conn->commit();
        header("Location: agencyprojects.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>Error deleting project: " . $e->getMessage() . "</div>";
    }
}

// If we get here without successful deletion, redirect back
header("Location: agencyprojects.php");
exit();
?>