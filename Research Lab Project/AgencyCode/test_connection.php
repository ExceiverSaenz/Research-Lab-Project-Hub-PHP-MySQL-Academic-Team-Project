<?php
require_once("../config.php");

try {
    // Test query
    $query = "SELECT * FROM agency";
    $result = $conn->query($query);

    echo "<h2>Testing Database Connection:</h2>";
    
    if ($result) {
        echo "<div style='color: green;'>? Connection successful!</div>";
        echo "<div>Found " . $result->num_rows . " agencies.</div>";
        echo "<h3>Agencies in database:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
    } else {
        echo "<div style='color: red;'>? Query failed: " . $conn->error . "</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>? Connection error: " . $e->getMessage() . "</div>";
}
?>