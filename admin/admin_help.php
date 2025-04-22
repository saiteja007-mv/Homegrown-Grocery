<?php
include '../config/db.php';
include '../includes/auth.php';

// Restrict access to admins
redirect_if_not_logged_in();
restrict_to_admin();

include '../includes/header_admin.php';

// Check if we need to refresh the page to show updated data
if (isset($_SESSION['refresh_help_tickets']) && $_SESSION['refresh_help_tickets'] === true) {
    unset($_SESSION['refresh_help_tickets']);
    header("Location: admin_help.php");
    exit();
}

// Handle ticket response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $ticket_id = $_POST['ticket_id'];
    $admin_response = $_POST['admin_response'];
    $status = $_POST['status'];
    $order_id = $_POST['order_id'];
    $user_id = $_POST['user_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update help ticket
        $stmt = $conn->prepare("UPDATE HelpTickets SET admin_response = ?, status = ? WHERE ticket_id = ?");
        $stmt->bind_param("ssi", $admin_response, $status, $ticket_id);
        $stmt->execute();

        // Handle order details updates
        if (isset($_POST['order_details']) && is_array($_POST['order_details'])) {
            // Initialize total with higher precision using string-based calculation
            $new_total = "0.00";
            
            // Debug log for tracking
            error_log("Starting order update process for order ID: $order_id");
            
            foreach ($_POST['order_details'] as $detail_id => $updates) {
                if (isset($updates['quantity'])) {
                    $detail_id = intval($detail_id);
                    $quantity = max(1, intval($updates['quantity'])); // Ensure quantity is at least 1
                    
                    // Get the current order detail information
                    $stmt = $conn->prepare("
                        SELECT product_name, price as current_price
                        FROM OrderDetails 
                        WHERE order_detail_id = ?
                    ");
                    $stmt->bind_param("i", $detail_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $product_name = '';
                    $current_price = 0.00;
                    
                    if ($row = $result->fetch_assoc()) {
                        $product_name = $row['product_name'];
                        $current_price = floatval($row['current_price']);
                        error_log("Retrieved product: {$row['product_name']}, Current price: {$row['current_price']}");
                    } else {
                        error_log("Warning: Order detail not found for ID: $detail_id");
                        continue;
                    }
                    
                    // Determine the price to use
                    $price = $current_price; // Default to current price
                    
                    // Use new price if provided in the form
                    if (isset($updates['price']) && !empty($updates['price']) && is_numeric($updates['price'])) {
                        $price = floatval($updates['price']);
                    }
                    
                    // Format price with proper decimal precision
                    $price = number_format($price, 2, '.', '');
                    
                    // Calculate item total
                    $item_total = number_format($quantity * (float)$price, 2, '.', '');
                    
                    error_log("Processing order detail ID: $detail_id, Product: $product_name, Quantity: $quantity, Price: $price, Total: $item_total");
                    
                    // Add to running total
                    $new_total = number_format((float)$new_total + (float)$item_total, 2, '.', '');
                    
                    // Update order detail
                    $stmt = $conn->prepare("
                        UPDATE OrderDetails 
                        SET quantity = ?, 
                            price = CAST(? AS DECIMAL(10,2)), 
                            total = CAST(? AS DECIMAL(10,2))
                        WHERE order_detail_id = ? AND order_id = ?
                    ");
                    
                    if ($stmt === false) {
                        throw new Exception("Error preparing update statement: " . $conn->error);
                    }
                    
                    $stmt->bind_param("issii", $quantity, $price, $item_total, $detail_id, $order_id);
                    
                    if (!$stmt->execute()) {
                        error_log("Error updating order detail: " . $stmt->error);
                        throw new Exception("Error updating order detail: " . $stmt->error);
                    }
                }
            }
            
            // Ensure proper decimal format for the final total
            $new_total = number_format((float)$new_total, 2, '.', '');
            error_log("Final calculated order total: $new_total for order ID: $order_id");

            // Debug total calculation
            error_log("New Order Total: " . $new_total);
            // Validate the total is a proper decimal value
            if (!is_numeric($new_total)) {
                throw new Exception("Invalid total amount calculated: $new_total");
            }
            
            // Update order total with correct decimal handling
            $stmt = $conn->prepare("UPDATE Orders SET total_amount = CAST(? AS DECIMAL(10,2)) WHERE order_id = ?");
            $stmt->bind_param("si", $new_total, $order_id);
            
            if (!$stmt->execute()) {
                error_log("Error updating order total: " . $stmt->error);
                throw new Exception("Error updating order total: " . $stmt->error);
            }
            
            // Verify the update was successful
            $verify_stmt = $conn->prepare("SELECT total_amount FROM Orders WHERE order_id = ?");
            $verify_stmt->bind_param("i", $order_id);
            $verify_stmt->execute();
            $result = $verify_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stored_total = $row['total_amount'];
                error_log("Verified stored total in database: $stored_total for order ID: $order_id");
                
                // Check if the stored value matches what we calculated
                if (abs((float)$stored_total - (float)$new_total) > 0.01) {
                    error_log("WARNING: Stored total ($stored_total) differs from calculated total ($new_total)");
                }
            }
            // Execute statement was already performed above - removing duplicate execution
            
            // Store the calculated total in session for verification
            $_SESSION['debug_new_total'] = $new_total;
            
            // Force refresh the page to show updated data
            $_SESSION['refresh_help_tickets'] = true;
        }

        // Handle shipping details updates if requested
        if (isset($_POST['update_shipping']) && $_POST['update_shipping'] == '1') {
            $shipping_updates = [
                'name' => $_POST['shipping_name'],
                'phone' => $_POST['shipping_phone'],
                'address' => $_POST['shipping_address'],
                'city' => $_POST['shipping_city'],
                'state' => $_POST['shipping_state'],
                'postal_code' => $_POST['shipping_postal_code'],
                'country' => $_POST['shipping_country']
            ];

            $stmt = $conn->prepare("
                UPDATE Shipping 
                SET name = ?, phone = ?, address = ?, city = ?, 
                    state = ?, postal_code = ?, country = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?
            ");
            $stmt->bind_param("sssssssi", 
                $shipping_updates['name'],
                $shipping_updates['phone'],
                $shipping_updates['address'],
                $shipping_updates['city'],
                $shipping_updates['state'],
                $shipping_updates['postal_code'],
                $shipping_updates['country'],
                $user_id
            );
            $stmt->execute();
        }

        $conn->commit();
        $_SESSION['success'] = "Ticket updated successfully.";
        
        // Set refresh flag to ensure updated data is shown
        $_SESSION['refresh_help_tickets'] = true;
        
        // Redirect to payment page if order total was updated
        if (isset($new_total) && $new_total > 0) {
            // Ensure amount parameter has correct decimal precision
            header("Location: ../payment.php?order_id=" . $order_id . "&amount=" . number_format($new_total, 2, '.', ''));
            exit();
        }
        
        header("Location: admin_help.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating ticket: " . $e->getMessage();
        header("Location: admin_help.php");
        exit();
    }
}

// Fetch all help tickets with user and order details
$stmt = $conn->prepare("
    SELECT t.*, u.username, u.email, u.user_id, o.order_date, o.total_amount,
           s.name as shipping_name, s.phone as shipping_phone, s.address as shipping_address,
           s.city as shipping_city, s.state as shipping_state, s.postal_code as shipping_postal_code,
           s.country as shipping_country
    FROM HelpTickets t 
    JOIN Users u ON t.user_id = u.user_id 
    JOIN Orders o ON t.order_id = o.order_id 
    LEFT JOIN Shipping s ON t.user_id = s.user_id
    ORDER BY 
        CASE t.status 
            WHEN 'open' THEN 1 
            WHEN 'in_progress' THEN 2 
            WHEN 'resolved' THEN 3 
        END,
        t.created_at DESC
");
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <h2><i class="bi bi-question-circle"></i> Manage Help Tickets</h2>

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
                                    (Order #<?php echo $ticket['order_id']; ?> - 
                                    <?php echo htmlspecialchars($ticket['username']); ?>)
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
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($ticket['username']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($ticket['email']); ?></p>
                                <p><strong>Order Date:</strong> <?php echo date('M j, Y', strtotime($ticket['order_date'])); ?></p>
                                <p><strong>Order Total:</strong> $<?php echo number_format($ticket['total_amount'], 2); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Ticket Information</h6>
                                <p><strong>Status:</strong> <?php echo ucfirst($ticket['status']); ?></p>
                                <p><strong>Submitted:</strong> <?php echo date('M j, Y, g:i a', strtotime($ticket['created_at'])); ?></p>
                                <p><strong>Last Updated:</strong> <?php echo date('M j, Y, g:i a', strtotime($ticket['updated_at'])); ?></p>
                            </div>
                        </div>

                        <hr>

                        <h6>Customer Message:</h6>
                        <p><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>

                        <?php if ($ticket['admin_response']): ?>
                            <hr>
                            <h6>Previous Response:</h6>
                            <p><?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?></p>
                        <?php endif; ?>

                        <hr>

                        <!-- Order Details Section -->
                        <h6>Order Items:</h6>
                        <?php
                        // Fetch the most recent order details after any updates
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
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                            <input type="hidden" name="order_id" value="<?php echo $ticket['order_id']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $ticket['user_id']; ?>">
                            
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Current Price</th>
                                            <th>Current Quantity</th>
                                            <th>Current Total</th>
                                            <th>New Price</th>
                                            <th>New Quantity</th>
                                            <th>New Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_details as $detail): ?>
                                            <tr data-order-id="<?php echo $ticket['order_id']; ?>">
                                                <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                                <td>$<?php echo number_format($detail['price'], 2); ?></td>
                                                <td><?php echo $detail['quantity']; ?></td>
                                                <td>$<?php echo number_format($detail['total'], 2); ?></td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" 
                                                           name="order_details[<?php echo $detail['order_detail_id']; ?>][price]" 
                                                           class="form-control form-control-sm price-input" 
                                                           value="<?php echo $detail['price']; ?>">
                                                </td>
                                                <td>
                                                    <input type="number" min="1" 
                                                           name="order_details[<?php echo $detail['order_detail_id']; ?>][quantity]" 
                                                           class="form-control form-control-sm quantity-input"
                                                           value="<?php echo $detail['quantity']; ?>">
                                                </td>
                                                <td class="new-total"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>New Order Total:</strong></td>
                                            <td id="new-order-total-<?php echo $ticket['order_id']; ?>" class="new-total"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Shipping Details Section -->
                            <h6>Shipping Details:</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="update_shipping<?php echo $ticket['ticket_id']; ?>" name="update_shipping" value="1">
                                        <label class="form-check-label" for="update_shipping<?php echo $ticket['ticket_id']; ?>">Update Shipping Details</label>
                                    </div>
                                    <div class="shipping-fields" style="display: none;">
                                        <div class="mb-2">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="shipping_name" class="form-control" value="<?php echo htmlspecialchars($ticket['shipping_name'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="shipping_phone" class="form-control" value="<?php echo htmlspecialchars($ticket['shipping_phone'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Address</label>
                                            <textarea name="shipping_address" class="form-control" rows="2"><?php echo htmlspecialchars($ticket['shipping_address'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">City</label>
                                                <input type="text" name="shipping_city" class="form-control" value="<?php echo htmlspecialchars($ticket['shipping_city'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">State</label>
                                                <input type="text" name="shipping_state" class="form-control" value="<?php echo htmlspecialchars($ticket['shipping_state'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" name="shipping_postal_code" class="form-control" value="<?php echo htmlspecialchars($ticket['shipping_postal_code'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Country</label>
                                                <input type="text" name="shipping_country" class="form-control" value="<?php echo htmlspecialchars($ticket['shipping_country'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status<?php echo $ticket['ticket_id']; ?>" class="form-label">Update Status</label>
                                        <select name="status" id="status<?php echo $ticket['ticket_id']; ?>" class="form-select" required>
                                            <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                            <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="new_total<?php echo $ticket['ticket_id']; ?>" class="form-label">New Order Total</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" min="0" name="new_total" id="new_total<?php echo $ticket['ticket_id']; ?>" 
                                                   class="form-control" value="<?php echo $ticket['total_amount']; ?>" placeholder="Enter new total">
                                        </div>
                                        <small class="text-muted">Update this if the total differs from the sum of items</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="response<?php echo $ticket['ticket_id']; ?>" class="form-label">Your Response</label>
                                <textarea name="admin_response" id="response<?php echo $ticket['ticket_id']; ?>" class="form-control" rows="4" required><?php echo htmlspecialchars($ticket['admin_response'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="respond_ticket" class="btn btn-primary">Submit Response & Update Order</button>
                        </form>

                        <script>
                            // Wait for DOM to be fully loaded
                            document.addEventListener('DOMContentLoaded', function() {
                                // Handle shipping checkbox
                                const shippingCheckbox = document.getElementById('update_shipping<?php echo $ticket['ticket_id']; ?>');
                                if (shippingCheckbox) {
                                    shippingCheckbox.addEventListener('change', function() {
                                        const shippingFields = this.closest('.col-md-6').querySelector('.shipping-fields');
                                        shippingFields.style.display = this.checked ? 'block' : 'none';
                                    });
                                }

                                // Format currency with 2 decimal places
                                function formatCurrency(amount) {
                                    return '$' + parseFloat(amount).toFixed(2);
                                }

                                // Parse currency string to float
                                function parseCurrency(value) {
                                    if (typeof value === 'string') {
                                        return parseFloat(value.replace(/[$,]/g, '')) || 0;
                                    }
                                    return parseFloat(value) || 0;
                                }

                                // Calculate total for a single row
                                function calculateRowTotal(row) {
                                    const priceInput = row.querySelector('.price-input');
                                    const quantityInput = row.querySelector('.quantity-input');
                                    const newTotalCell = row.querySelector('.new-total');

                                    if (!priceInput || !quantityInput || !newTotalCell) return 0;

                                    const price = parseCurrency(priceInput.value);
                                    const quantity = parseInt(quantityInput.value) || 1;
                                    const total = price * quantity;

                                    newTotalCell.textContent = formatCurrency(total);
                                    return total;
                                }

                                // Update the entire order total
                                function updateOrderTotal(orderId) {
                                    const rows = document.querySelectorAll(`tr[data-order-id="${orderId}"]`);
                                    let orderTotal = 0;

                                    rows.forEach(row => {
                                        orderTotal += calculateRowTotal(row);
                                    });

                                    // Update the total in the table footer
                                    const totalCell = document.getElementById(`new-order-total-${orderId}`);
                                    if (totalCell) {
                                        totalCell.textContent = formatCurrency(orderTotal);
                                    }

                                    // Update the hidden input field
                                    const totalInput = document.getElementById(`new_total${orderId}`);
                                    if (totalInput) {
                                        totalInput.value = orderTotal.toFixed(2);
                                    }
                                }

                                // Initialize all rows in the table
                                function initializeTable() {
                                    const rows = document.querySelectorAll('tr[data-order-id]');
                                    const processedOrders = new Set();

                                    rows.forEach(row => {
                                        const orderId = row.dataset.orderId;
                                        const priceInput = row.querySelector('.price-input');
                                        const quantityInput = row.querySelector('.quantity-input');
                                        const currentPriceCell = row.cells[1]; // Current Price column

                                        // Set initial price if empty
                                        if (priceInput && currentPriceCell) {
                                            const currentPrice = parseCurrency(currentPriceCell.textContent);
                                            if (!priceInput.value) {
                                                priceInput.value = currentPrice.toFixed(2);
                                            }
                                        }

                                        // Add event listeners for real-time updates
                                        if (priceInput) {
                                            priceInput.addEventListener('input', () => {
                                                calculateRowTotal(row);
                                                updateOrderTotal(orderId);
                                            });
                                        }

                                        if (quantityInput) {
                                            quantityInput.addEventListener('input', () => {
                                                calculateRowTotal(row);
                                                updateOrderTotal(orderId);
                                            });
                                        }

                                        // Calculate initial totals only once per order
                                        if (!processedOrders.has(orderId)) {
                                            processedOrders.add(orderId);
                                            updateOrderTotal(orderId);
                                        }
                                    });
                                }

                                // Initialize the table
                                initializeTable();
                            });
                        </script>
                    <!-- Debug information to help troubleshoot calculations -->
                    <?php if (isset($_SESSION['debug_new_total'])): ?>
                    <div class="mt-3 p-2 bg-light border small">
                        <p class="mb-1"><strong>Debug Info:</strong> Last calculated total: $<?php echo $_SESSION['debug_new_total']; unset($_SESSION['debug_new_total']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 