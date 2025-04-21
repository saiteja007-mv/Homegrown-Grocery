<?php
require_once 'config/db.php';

try {
    echo "<h2>Fixing Orders Table</h2>";
    
    // Check if status column exists
    $result = $conn->query("SHOW COLUMNS FROM Orders LIKE 'status'");
    if ($result->num_rows == 0) {
        echo "Status column does not exist. Adding it...<br>";
        
        // Add status column
        $sql = "ALTER TABLE Orders ADD COLUMN status ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending' AFTER order_date";
        if ($conn->query($sql)) {
            echo "Status column added successfully<br>";
        } else {
            throw new Exception("Error adding status column: " . $conn->error);
        }
    } else {
        echo "Status column already exists<br>";
    }
    
    // Verify table structure
    echo "<h3>Current Orders Table Structure</h3>";
    $result = $conn->query("DESCRIBE Orders");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test inserting an order
    echo "<h3>Testing Order Insert</h3>";
    $test_sql = "INSERT INTO Orders (user_id, total_amount, order_date, status) VALUES (1, 10.00, NOW(), 'pending')";
    if ($conn->query($test_sql)) {
        echo "Test order inserted successfully. Insert ID: " . $conn->insert_id . "<br>";
        // Clean up test data
        $conn->query("DELETE FROM Orders WHERE order_id = " . $conn->insert_id);
        echo "Test data cleaned up<br>";
    } else {
        throw new Exception("Error inserting test order: " . $conn->error);
    }
    
    echo "<h3>Orders Table Fixed Successfully!</h3>";
    echo "<p>You can now <a href='checkout.php'>return to checkout</a> and place your order.</p>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?> 