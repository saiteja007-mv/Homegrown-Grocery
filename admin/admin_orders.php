<?php
include '../config/db.php';
include '../includes/auth.php';

// Restrict access to admins
redirect_if_not_logged_in();
restrict_to_admin();

include '../includes/header_admin.php';

// Check if status column exists and add it if it doesn't
$check_column = $conn->query("SHOW COLUMNS FROM Orders LIKE 'status'");
if ($check_column->num_rows === 0) {
    // Add status column if it doesn't exist
    $alter_sql = "ALTER TABLE Orders ADD COLUMN status ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending' AFTER order_date";
    if (!$conn->query($alter_sql)) {
        $_SESSION['error'] = "Error adding status column: " . $conn->error;
    } else {
        // Update existing orders to have 'pending' status
        $conn->query("UPDATE Orders SET status = 'pending' WHERE status IS NULL");
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete related help tickets
        $stmt = $conn->prepare("DELETE FROM HelpTickets WHERE order_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing delete help tickets statement: " . $conn->error);
        }
        $stmt->bind_param("i", $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting help tickets: " . $stmt->error);
        }

        // Then delete related order details
        $stmt = $conn->prepare("DELETE FROM OrderDetails WHERE order_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing delete order details statement: " . $conn->error);
        }
        $stmt->bind_param("i", $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting order details: " . $stmt->error);
        }
        
        // Finally delete the order
        $stmt = $conn->prepare("DELETE FROM Orders WHERE order_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing delete order statement: " . $conn->error);
        }
        $stmt->bind_param("i", $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting order: " . $stmt->error);
        }
        
        // If we got here, all deletes were successful
        $conn->commit();
        $_SESSION['success'] = "Order and all related records deleted successfully.";
        header("Location: admin_orders.php");
        exit;
        
    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        $conn->rollback();
        $_SESSION['error'] = "Failed to delete order: " . $e->getMessage();
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    $order_id = intval($_POST['order_id']);
    $total_amount = floatval($_POST['total_amount']);
    $status = $_POST['status'];
    
    // Verify status is valid
    $valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status value.";
    } else {
        $stmt = $conn->prepare("UPDATE Orders SET total_amount = ?, status = ? WHERE order_id = ?");
        $stmt->bind_param("dsi", $total_amount, $status, $order_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Order updated successfully.";
            header("Location: admin_orders.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update order.";
        }
    }
}

// Fetch all orders with user details
$query = "SELECT o.*, u.username, u.email FROM Orders o 
          JOIN Users u ON o.user_id = u.user_id 
          ORDER BY o.order_date DESC";

$result = $conn->query($query);
if (!$result) {
    die("Error fetching orders: " . $conn->error);
}
?>

<div class="container mt-4">
    <h2><i class="bi bi-receipt"></i> Manage Orders</h2>

    <!-- Alerts -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['order_id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['username']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                        </td>
                        <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo date('M j, Y, g:i a', strtotime($row['order_date'])); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo isset($row['status']) ? (
                                    $row['status'] === 'pending' ? 'warning' : 
                                    ($row['status'] === 'confirmed' ? 'info' : 
                                    ($row['status'] === 'shipped' ? 'primary' : 
                                    ($row['status'] === 'delivered' ? 'success' : 'danger')))
                                ) : 'secondary'; 
                            ?>">
                                <?php echo isset($row['status']) ? ucfirst($row['status']) : 'Processing'; ?>
                            </span>
                        </td>
                        <td>
                            <!-- Edit Button -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['order_id']; ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>

                            <!-- Delete Button -->
                            <a href="admin_orders.php?delete=<?php echo $row['order_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?');">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $row['order_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['order_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel<?php echo $row['order_id']; ?>">Edit Order</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <div class="mb-3">
                                            <label for="total_amount<?php echo $row['order_id']; ?>" class="form-label">Total Amount</label>
                                            <input type="number" name="total_amount" id="total_amount<?php echo $row['order_id']; ?>" class="form-control" value="<?php echo $row['total_amount']; ?>" step="0.01" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="status<?php echo $row['order_id']; ?>" class="form-label">Order Status</label>
                                            <select name="status" id="status<?php echo $row['order_id']; ?>" class="form-select" required>
                                                <option value="pending" <?php echo (isset($row['status']) && $row['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo (isset($row['status']) && $row['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="shipped" <?php echo (isset($row['status']) && $row['status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo (isset($row['status']) && $row['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo (isset($row['status']) && $row['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="edit_order" class="btn btn-primary">Save Changes</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
