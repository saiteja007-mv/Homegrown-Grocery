<?php
session_start();
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];

// Handle new ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $order_id = intval($_POST['order_id']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Verify that the order belongs to the user
    $stmt = $conn->prepare("SELECT order_id FROM Orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO HelpTickets (user_id, order_id, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $order_id, $subject, $message);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Your help ticket has been submitted successfully.";
        } else {
            $_SESSION['error'] = "Failed to submit help ticket. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Invalid order ID. Please try again.";
    }
    
    header("Location: help.php");
    exit;
}

// Fetch user's orders for the dropdown
$stmt = $conn->prepare("SELECT order_id, order_date, total_amount FROM Orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch user's help tickets
$stmt = $conn->prepare("
    SELECT t.*, o.order_date, o.total_amount, o.order_id,
           (SELECT COUNT(*) FROM OrderDetails WHERE order_id = o.order_id) as item_count
    FROM HelpTickets t 
    JOIN Orders o ON t.order_id = o.order_id 
    WHERE t.user_id = ? 
    ORDER BY t.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2><i class="bi bi-question-circle"></i> Help Center</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Submit New Ticket -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Submit a Help Ticket</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="order_id" class="form-label">Select Order</label>
                            <select name="order_id" id="order_id" class="form-select" required>
                                <option value="">Choose an order...</option>
                                <?php foreach ($orders as $order): ?>
                                    <option value="<?php echo $order['order_id']; ?>">
                                        Order #<?php echo $order['order_id']; ?> - 
                                        $<?php echo number_format($order['total_amount'], 2); ?> - 
                                        <?php echo date('M j, Y', strtotime($order['order_date'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" name="subject" id="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="submit_ticket" class="btn btn-primary">Submit Ticket</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Your Tickets -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Help Tickets</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <p class="text-muted">You haven't submitted any help tickets yet.</p>
                    <?php else: ?>
                        <div class="accordion" id="ticketsAccordion">
                            <?php foreach ($tickets as $ticket): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $ticket['ticket_id']; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?php echo $ticket['ticket_id']; ?>">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <span>
                                                    <?php echo htmlspecialchars($ticket['subject']); ?>
                                                    <small class="text-muted">
                                                        (Order #<?php echo $ticket['order_id']; ?>)
                                                    </small>
                                                </span>
                                                <span class="badge bg-<?php 
                                                    echo $ticket['status'] === 'open' ? 'danger' : 
                                                        ($ticket['status'] === 'in_progress' ? 'warning' : 'success'); 
                                                ?>">
                                                    <?php echo ucfirst($ticket['status']); ?>
                                                </span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $ticket['ticket_id']; ?>" class="accordion-collapse collapse" 
                                         data-bs-parent="#ticketsAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Your Message:</strong></p>
                                            <p><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
                                            
                                            <?php if ($ticket['admin_response']): ?>
                                                <hr>
                                                <p><strong>Admin Response:</strong></p>
                                                <p><?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?></p>

                                                <!-- Order Details Section -->
                                                <hr>
                                                <h6>Order Details:</h6>
                                                <?php
                                                // Fetch current order details
                                                $stmt = $conn->prepare("
                                                    SELECT od.*, o.total_amount as current_total, o.order_date
                                                    FROM OrderDetails od
                                                    JOIN Orders o ON od.order_id = o.order_id
                                                    WHERE od.order_id = ?
                                                ");
                                                $stmt->bind_param("i", $ticket['order_id']);
                                                $stmt->execute();
                                                $order_details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                                ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Product</th>
                                                                <th>Price</th>
                                                                <th>Quantity</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($order_details as $detail): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                                                    <td>$<?php echo number_format($detail['price'], 2); ?></td>
                                                                    <td><?php echo $detail['quantity']; ?></td>
                                                                    <td>$<?php echo number_format($detail['total'], 2); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <td colspan="3" class="text-end"><strong>Order Total:</strong></td>
                                                                <td><strong>$<?php echo number_format($order_details[0]['current_total'], 2); ?></strong></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                                <small class="text-muted">
                                                    Last updated: <?php echo date('M j, Y, g:i a', strtotime($order_details[0]['order_date'])); ?>
                                                </small>
                                            <?php endif; ?>
                                            
                                            <small class="text-muted">
                                                Submitted on <?php echo date('M j, Y, g:i a', strtotime($ticket['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 