<?php
include 'config/db.php';
include 'includes/header.php';

if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Get order details and shipping address
$stmt = $conn->prepare("SELECT o.total_amount, o.order_date, u.shipping_address, u.phone 
FROM Orders o 
JOIN Users u ON o.user_id = u.user_id 
WHERE o.order_id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Get order items
$stmt = $conn->prepare("SELECT product_name, price, quantity FROM OrderDetails WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<div class="container mt-4">
    <div class="text-center mb-4">
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for shopping with HomeGrown. Your order ID is <strong>#<?php echo $order_id; ?></strong>.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                    <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                    <p><strong>Contact Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>

                    <h6 class="mt-4 mb-3">Items Ordered:</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="orders.php" class="btn btn-primary">View Your Orders</a>
                <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
