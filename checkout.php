<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];

// Fetch existing shipping details
$stmt = $conn->prepare("SELECT * FROM Shipping WHERE user_id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$shipping_result = $stmt->get_result();
$shipping = $shipping_result->fetch_assoc();

// Calculate total from the cart
$cart_total = 0;
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT name, new_price FROM Products WHERE product_id = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        if ($product) {
            $item_total = $product['new_price'] * $quantity;
            $cart_total += $item_total;

            $cart_items[] = [
                'name' => $product['name'],
                'quantity' => $quantity,
                'price' => $product['new_price'],
                'total' => $item_total
            ];
        }
    }
} else {
    // Redirect to cart page if no items in the cart
    header('Location: cart.php');
    exit;
}

// Handle shipping details form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_shipping'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);

    if ($shipping) {
        // Update existing shipping details
        $stmt = $conn->prepare("UPDATE Shipping SET name = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ?, country = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->bind_param("sssssssi", $name, $phone, $address, $city, $state, $postal_code, $country, $user_id);
    } else {
        // Insert new shipping details
        $stmt = $conn->prepare("INSERT INTO Shipping (user_id, name, phone, address, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $name, $phone, $address, $city, $state, $postal_code, $country);
    }

    if ($stmt->execute()) {
        header("Location: checkout.php");
        exit;
    } else {
        $error = "Failed to save shipping details. Please try again.";
    }
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!$shipping) {
        $error = "Please fill in your shipping details before placing the order.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Debug information
            error_log("Attempting to create order for user_id: " . $user_id);
            error_log("Cart total: " . $cart_total);
            
            // First verify the table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'Orders'");
            if ($table_check->num_rows == 0) {
                throw new Exception("Orders table does not exist!");
            }
            
            // Check if status column exists
            $column_check = $conn->query("SHOW COLUMNS FROM Orders LIKE 'status'");
            $has_status_column = ($column_check->num_rows > 0);
            
            // Try a direct insert first instead of prepared statement
            if ($has_status_column) {
                $order_sql = "INSERT INTO Orders (user_id, total_amount, order_date, status) VALUES ($user_id, $cart_total, NOW(), 'pending')";
            } else {
                $order_sql = "INSERT INTO Orders (user_id, total_amount, order_date) VALUES ($user_id, $cart_total, NOW())";
            }
            
            error_log("Order SQL: " . $order_sql);
            
            $result = $conn->query($order_sql);
            if (!$result) {
                throw new Exception("Error creating order: " . $conn->error);
            }
            
            $order_id = $conn->insert_id;
            error_log("Order created successfully with ID: " . $order_id);

            // Save each cart item as an order detail
            foreach ($cart_items as $item) {
                error_log("Processing item: " . print_r($item, true));
                
                // Use direct insert for order details as well
                $detail_sql = "INSERT INTO OrderDetails (order_id, product_name, price, quantity, total) 
                               VALUES ($order_id, '" . $conn->real_escape_string($item['name']) . "', 
                               {$item['price']}, {$item['quantity']}, {$item['total']})";
                
                error_log("Order Details SQL: " . $detail_sql);
                
                if (!$conn->query($detail_sql)) {
                    throw new Exception("Error creating order detail: " . $conn->error);
                }
            }

            // Clear cart from database
            $clear_sql = "DELETE FROM Cart WHERE user_id = $user_id";
            error_log("Clear Cart SQL: " . $clear_sql);
            
            if (!$conn->query($clear_sql)) {
                throw new Exception("Error clearing cart: " . $conn->error);
            }

            // Commit transaction
            $conn->commit();
            error_log("Transaction committed successfully");

            // Clear cart session
            unset($_SESSION['cart']);

            // Redirect to success page
            header("Location: order_success.php?order_id=$order_id");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            error_log("Error in checkout process: " . $e->getMessage());
            $error = "Failed to place the order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - HomeGrown</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h2><i class="bi bi-cart-check"></i> Checkout</h2>

    <div class="row">
        <!-- Shipping Details Form -->
        <div class="col-md-6">
            <h4>Shipping Details</h4>
            <form method="POST" class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($shipping['name'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($shipping['phone'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea name="address" id="address" class="form-control" rows="3" required><?php echo htmlspecialchars($shipping['address'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="city" id="city" class="form-control" value="<?php echo htmlspecialchars($shipping['city'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="state" class="form-label">State</label>
                    <input type="text" name="state" id="state" class="form-control" value="<?php echo htmlspecialchars($shipping['state'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="postal_code" class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" id="postal_code" class="form-control" value="<?php echo htmlspecialchars($shipping['postal_code'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" name="country" id="country" class="form-control" value="<?php echo htmlspecialchars($shipping['country'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <button type="submit" name="update_shipping" class="btn btn-success w-100">Save Shipping Details</button>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="col-md-6">
            <h4>Order Summary</h4>
            <ul class="list-group">
                <?php foreach ($cart_items as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)
                        <span>$<?php echo number_format($item['total'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Total</strong>
                    <span><strong>$<?php echo number_format($cart_total, 2); ?></strong></span>
                </li>
            </ul>
            <form method="POST" class="mt-4">
                <button type="submit" name="place_order" class="btn btn-primary w-100">Place Order</button>
            </form>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
