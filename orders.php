<?php
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user with shipping details
$stmt = $conn->prepare("SELECT o.*, u.shipping_address, u.phone, u.username, s.name as shipping_name, 
                        s.phone as shipping_phone, s.address as shipping_address_full, 
                        s.city, s.state, s.postal_code, s.country 
                        FROM Orders o 
                        JOIN Users u ON o.user_id = u.user_id 
                        LEFT JOIN Shipping s ON o.user_id = s.user_id 
                        WHERE o.user_id = ? 
                        ORDER BY o.order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <h2><i class="bi bi-box-seam"></i> Your Orders</h2>
    <?php if (count($orders) > 0): ?>
        <div class="accordion" id="ordersAccordion">
            <?php foreach ($orders as $order): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $order['order_id']; ?>">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $order['order_id']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $order['order_id']; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <span>
                                    Order #<?php echo $order['order_id']; ?> - 
                                    $<?php echo number_format($order['total_amount'], 2); ?> - 
                                    <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?>
                                </span>
                                <span class="badge bg-<?php 
                                    echo isset($order['status']) ? (
                                        $order['status'] === 'pending' ? 'warning' : 
                                        ($order['status'] === 'confirmed' ? 'info' : 
                                        ($order['status'] === 'shipped' ? 'primary' : 
                                        ($order['status'] === 'delivered' ? 'success' : 'danger')))
                                    ) : 'secondary'; 
                                ?>">
                                    <?php echo isset($order['status']) ? ucfirst($order['status']) : 'Processing'; ?>
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $order['order_id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $order['order_id']; ?>">
                        <div class="accordion-body">
                            <!-- Order Status Timeline -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Order Status</h5>
                                </div>
                                <div class="card-body">
                                    <div class="order-status-timeline">
                                        <div class="status-step <?php 
                                            $status = isset($order['status']) ? $order['status'] : 'pending';
                                            echo $status === 'pending' ? 'active' : 
                                                 ($status === 'confirmed' || $status === 'shipped' || $status === 'delivered' ? 'completed' : ''); 
                                        ?>">
                                            <i class="bi bi-cart-check"></i>
                                            <span>Order Placed</span>
                                        </div>
                                        <div class="status-step <?php 
                                            echo $status === 'confirmed' ? 'active' : 
                                                 ($status === 'shipped' || $status === 'delivered' ? 'completed' : ''); 
                                        ?>">
                                            <i class="bi bi-check-circle"></i>
                                            <span>Order Confirmed</span>
                                        </div>
                                        <div class="status-step <?php 
                                            echo $status === 'shipped' ? 'active' : 
                                                 ($status === 'delivered' ? 'completed' : ''); 
                                        ?>">
                                            <i class="bi bi-truck"></i>
                                            <span>Shipped</span>
                                        </div>
                                        <div class="status-step <?php 
                                            echo $status === 'delivered' ? 'active' : ''; 
                                        ?>">
                                            <i class="bi bi-house-check"></i>
                                            <span>Delivered</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-person"></i> Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                                </div>
                            </div>

                            <!-- Shipping Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-truck"></i> Shipping Information</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($order['shipping_name'])): ?>
                                        <p><strong>Recipient Name:</strong> <?php echo htmlspecialchars($order['shipping_name']); ?></p>
                                        <p><strong>Contact Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['shipping_address_full']); ?></p>
                                        <p><strong>City:</strong> <?php echo htmlspecialchars($order['city']); ?></p>
                                        <p><strong>State/Province:</strong> <?php echo htmlspecialchars($order['state']); ?></p>
                                        <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($order['postal_code']); ?></p>
                                        <p><strong>Country:</strong> <?php echo htmlspecialchars($order['country']); ?></p>
                                    <?php else: ?>
                                        <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address'] ?? 'Not provided'); ?></p>
                                        <p><strong>Contact Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'Not provided'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Payment Method:</strong> Standard Payment</p>
                                    <p><strong>Payment Status:</strong> <span class="badge bg-success">Paid</span></p>
                                    <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-cart-check"></i> Order Items</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM OrderDetails WHERE order_id = ?");
                                                $stmt->bind_param("i", $order['order_id']);
                                                $stmt->execute();
                                                $details_result = $stmt->get_result();
                                                while ($detail = $details_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                                        <td>$<?php echo number_format($detail['price'], 2); ?></td>
                                                        <td><?php echo $detail['quantity']; ?></td>
                                                        <td>$<?php echo number_format($detail['total'], 2); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You have no orders yet.</div>
    <?php endif; ?>
</div>

<style>
.order-status-timeline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    position: relative;
}

.order-status-timeline::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.status-step {
    position: relative;
    z-index: 2;
    background: white;
    padding: 10px;
    text-align: center;
    min-width: 100px;
}

.status-step i {
    font-size: 24px;
    margin-bottom: 5px;
    color: #6c757d;
}

.status-step span {
    display: block;
    font-size: 12px;
    color: #6c757d;
}

.status-step.completed i,
.status-step.completed span {
    color: #198754;
}

.status-step.active i,
.status-step.active span {
    color: #0d6efd;
    font-weight: bold;
}
</style>

<?php include 'includes/footer.php'; ?>
