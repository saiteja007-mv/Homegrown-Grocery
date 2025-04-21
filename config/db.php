<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "nestco_homegrown";

// Create connection with error handling
try {
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    if (!$conn->select_db($dbname)) {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Set charset to ensure proper encoding
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
