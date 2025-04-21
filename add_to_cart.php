<?php
session_start();
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();

if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']);
    $user_id = $_SESSION['user_id'];
    
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT quantity FROM Products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        if ($product['quantity'] > 0) {
            // Check if product already in cart
            $stmt = $conn->prepare("SELECT quantity FROM Cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $cart_result = $stmt->get_result();
            
            if ($cart_item = $cart_result->fetch_assoc()) {
                // Update quantity if already in cart
                $new_quantity = $cart_item['quantity'] + 1;
                $stmt = $conn->prepare("UPDATE Cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
            } else {
                // Add new item to cart
                $stmt = $conn->prepare("INSERT INTO Cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $stmt->bind_param("ii", $user_id, $product_id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Item added to cart successfully!";
                // Update session cart
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]++;
                } else {
                    $_SESSION['cart'][$product_id] = 1;
                }
            } else {
                $_SESSION['error'] = "Failed to add item to cart.";
            }
        } else {
            $_SESSION['error'] = "Sorry, this item is out of stock.";
        }
    } else {
        $_SESSION['error'] = "Product not found.";
    }
}

// Redirect back to previous page
$redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: " . $redirect_to);
exit;
?>