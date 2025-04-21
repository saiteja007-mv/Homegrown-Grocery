<?php
include '../config/db.php';
include '../includes/auth.php';

redirect_if_not_logged_in();
restrict_to_admin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM Products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: admin_products.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
