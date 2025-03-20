<?php
session_start();


$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the main login page (index.php)
header("Location: ../index.php");
exit();