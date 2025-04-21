<?php
require_once 'config/db.php';

try {
    // Test database connection
    echo "<h3>Database Connection Test</h3>";
    echo "Connected to MySQL server version: " . $conn->server_info . "<br>";
    echo "Current database: " . $conn->database . "<br><br>";
    
    // Test Orders table
    echo "<h3>Orders Table Test</h3>";
    $result = $conn->query("SHOW CREATE TABLE Orders");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Orders table structure:<br>";
        echo "<pre>" . $row['Create Table'] . "</pre>";
    } else {
        echo "Error getting Orders table structure: " . $conn->error . "<br>";
    }
    
    // Test inserting a sample order
    echo "<h3>Test Order Insert</h3>";
    $test_sql = "INSERT INTO Orders (user_id, total_amount, order_date, status) VALUES (1, 10.00, NOW(), 'pending')";
    if ($conn->query($test_sql)) {
        echo "Test order inserted successfully<br>";
        // Clean up test data
        $conn->query("DELETE FROM Orders WHERE user_id = 1 AND total_amount = 10.00");
    } else {
        echo "Error inserting test order: " . $conn->error . "<br>";
    }
    
    // Show table status
    echo "<h3>Table Status</h3>";
    $result = $conn->query("SHOW TABLE STATUS LIKE 'Orders'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Table status:<br>";
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    die("Test error: " . $e->getMessage());
}
?> 