<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session and include required files
session_start();
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];

// Load cart items from the database into the session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $stmt = $conn->prepare("SELECT product_id, quantity FROM Cart WHERE user_id = ?");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
    }
}

// Handle increasing quantity
if (isset($_GET['increase'])) {
    $product_id = intval($_GET['increase']);
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += 1;

        // Update database
        $quantity = $_SESSION['cart'][$product_id];
        $stmt = $conn->prepare("UPDATE Cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit;
}

// Handle decreasing quantity
if (isset($_GET['decrease'])) {
    $product_id = intval($_GET['decrease']);
    if (isset($_SESSION['cart'][$product_id]) && $_SESSION['cart'][$product_id] > 1) {
        $_SESSION['cart'][$product_id] -= 1;

        // Update database
        $quantity = $_SESSION['cart'][$product_id];
        $stmt = $conn->prepare("UPDATE Cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit;
}

// Handle removing items from the cart
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);

        // Remove from database
        $stmt = $conn->prepare("DELETE FROM Cart WHERE user_id = ? AND product_id = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit;
}

// Calculate total and fetch cart items
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
        if ($product = $result->fetch_assoc()) {
            $item_total = $product['new_price'] * $quantity;
            $cart_total += $item_total;

            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['new_price'],
                'quantity' => $quantity,
                'total' => $item_total
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - HomeGrown</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .empty-cart {
            text-align: center;
            margin-top: 50px;
        }
        .cart-table {
            margin-top: 30px;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2><i class="bi bi-cart"></i> Your Cart</h2>

        <?php if (!empty($cart_items)): ?>
            <table class="table table-bordered cart-table">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <div class="quantity-controls">
                                    <a href="cart.php?decrease=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-dash-circle"></i>
                                    </a>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <a href="cart.php?increase=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['total'], 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Remove
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end">
                <h4>Total: $<?php echo number_format($cart_total, 2); ?></h4>
                <a href="checkout.php" class="btn btn-success"><i class="bi bi-credit-card"></i> Checkout</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info empty-cart">
                <h4>Your cart is empty!</h4>
                <p><a href="index.php" class="btn btn-primary"><i class="bi bi-cart-plus"></i> Add Items</a></p>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
