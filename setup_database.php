<?php
require_once 'config/db.php';

try {
    // Verify database exists
    $result = $conn->query("SELECT DATABASE()");
    $row = $result->fetch_row();
    echo "Current database: " . $row[0] . "<br>";
    
    // Create Orders table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `Orders` (
        `order_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `total_amount` decimal(10,2) NOT NULL,
        `order_date` datetime NOT NULL,
        `status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
        PRIMARY KEY (`order_id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating Orders table: " . $conn->error);
    }
    
    // Verify Orders table structure
    $result = $conn->query("DESCRIBE Orders");
    echo "<br>Orders table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo print_r($row, true) . "<br>";
    }
    
    // Create OrderDetails table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `OrderDetails` (
        `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `product_name` varchar(255) NOT NULL,
        `price` decimal(10,2) NOT NULL,
        `quantity` int(11) NOT NULL,
        `total` decimal(10,2) NOT NULL,
        PRIMARY KEY (`order_detail_id`),
        KEY `order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating OrderDetails table: " . $conn->error);
    }
    
    // Verify OrderDetails table structure
    $result = $conn->query("DESCRIBE OrderDetails");
    echo "<br>OrderDetails table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo print_r($row, true) . "<br>";
    }
    
    echo "<br>Database tables created and verified successfully!";
    
} catch (Exception $e) {
    die("Setup error: " . $e->getMessage());
}
?> 