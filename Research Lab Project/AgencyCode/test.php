<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");

if ($conn) {
    echo "Database connection successful!";
    
    // Test query
    $query = "SELECT * FROM agency";
    $result = $conn->query($query);
    
    if ($result) {
        echo "<br>Found " . $result->num_rows . " agencies";
        while ($row = $result->fetch_assoc()) {
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
    } else {
        echo "<br>Query failed: " . $conn->error;
    }
} else {
    echo "Connection failed: " . mysqli_connect_error();
}
?>