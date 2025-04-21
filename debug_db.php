<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "nestco_homegrown";

echo "<h2>Database Debug Information</h2>";

// Test connection without database
echo "<h3>1. Testing connection to MySQL server</h3>";
try {
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Successfully connected to MySQL server<br>";
    echo "Server version: " . $conn->server_info . "<br>";
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}

// Test database existence
echo "<h3>2. Testing database existence</h3>";
try {
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows == 0) {
        echo "Database '$dbname' does not exist. Creating it...<br>";
        if ($conn->query("CREATE DATABASE `$dbname`")) {
            echo "Database created successfully<br>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    } else {
        echo "Database '$dbname' exists<br>";
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Select database
echo "<h3>3. Selecting database</h3>";
try {
    if ($conn->select_db($dbname)) {
        echo "Successfully selected database '$dbname'<br>";
    } else {
        throw new Exception("Error selecting database: " . $conn->error);
    }
} catch (Exception $e) {
    die("Database selection error: " . $e->getMessage());
}

// Test Orders table
echo "<h3>4. Testing Orders table</h3>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'Orders'");
    if ($result->num_rows == 0) {
        echo "Orders table does not exist. Creating it...<br>";
        
        $sql = "CREATE TABLE `Orders` (
            `order_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `total_amount` decimal(10,2) NOT NULL,
            `order_date` datetime NOT NULL,
            `status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
            PRIMARY KEY (`order_id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        if ($conn->query($sql)) {
            echo "Orders table created successfully<br>";
        } else {
            throw new Exception("Error creating Orders table: " . $conn->error);
        }
    } else {
        echo "Orders table exists<br>";
    }
    
    // Show table structure
    $result = $conn->query("DESCRIBE Orders");
    echo "Orders table structure:<br>";
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
} catch (Exception $e) {
    die("Orders table error: " . $e->getMessage());
}

// Test direct insert
echo "<h3>5. Testing direct insert</h3>";
try {
    $test_sql = "INSERT INTO Orders (user_id, total_amount, order_date, status) VALUES (1, 10.00, NOW(), 'pending')";
    if ($conn->query($test_sql)) {
        echo "Direct insert successful. Insert ID: " . $conn->insert_id . "<br>";
        // Clean up test data
        $conn->query("DELETE FROM Orders WHERE order_id = " . $conn->insert_id);
        echo "Test data cleaned up<br>";
    } else {
        throw new Exception("Error with direct insert: " . $conn->error);
    }
} catch (Exception $e) {
    echo "Direct insert error: " . $e->getMessage() . "<br>";
}

// Test prepared statement
echo "<h3>6. Testing prepared statement</h3>";
try {
    $stmt = $conn->prepare("INSERT INTO Orders (user_id, total_amount, order_date, status) VALUES (?, ?, NOW(), 'pending')");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $user_id = 1;
    $total_amount = 20.00;
    
    $stmt->bind_param("id", $user_id, $total_amount);
    if ($stmt->execute()) {
        echo "Prepared statement insert successful. Insert ID: " . $stmt->insert_id . "<br>";
        // Clean up test data
        $conn->query("DELETE FROM Orders WHERE order_id = " . $stmt->insert_id);
        echo "Test data cleaned up<br>";
    } else {
        throw new Exception("Error executing prepared statement: " . $stmt->error);
    }
} catch (Exception $e) {
    echo "Prepared statement error: " . $e->getMessage() . "<br>";
}

echo "<h3>7. Database connection information</h3>";
echo "Host info: " . $conn->host_info . "<br>";
echo "Protocol version: " . $conn->protocol_version . "<br>";
echo "Client info: " . $conn->client_info . "<br>";
echo "Character set: " . $conn->character_set_name() . "<br>";

// Close connection
$conn->close();
echo "<br>Database connection closed";
?> 