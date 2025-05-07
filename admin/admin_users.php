<?php
include '../config/db.php';
include '../includes/auth.php';

// Restrict access to admins
redirect_if_not_logged_in();
restrict_to_admin();

include '../includes/header_admin.php';

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if user exists
        $check_user = $conn->prepare("SELECT user_id FROM Users WHERE user_id = ?");
        $check_user->bind_param("i", $user_id);
        $check_user->execute();
        $user_result = $check_user->get_result();
        
        if ($user_result->num_rows === 0) {
            throw new Exception("User not found");
        }

        // Delete from HelpTickets first since it references Orders
        $stmt = $conn->prepare("DELETE FROM HelpTickets WHERE user_id = ? OR order_id IN (SELECT order_id FROM Orders WHERE user_id = ?)");
        $stmt->bind_param("ii", $user_id, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete from HelpTickets: " . $stmt->error);
        }

        // Delete from OrderDetails next
        $stmt = $conn->prepare("DELETE od FROM OrderDetails od 
                               INNER JOIN Orders o ON od.order_id = o.order_id 
                               WHERE o.user_id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete from OrderDetails: " . $stmt->error);
        }

        // Delete from Orders since dependencies are cleared
        $stmt = $conn->prepare("DELETE FROM Orders WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete from Orders: " . $stmt->error);
        }

        // Delete from remaining tables
        $tables = ['Cart', 'Shipping'];
        foreach ($tables as $table) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete from $table table: " . $stmt->error);
            }
        }
        
        // Finally delete the user
        $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete user: " . $stmt->error);
        }

        $conn->commit();
        $_SESSION['success'] = "User and all related records deleted successfully.";
        header("Location: admin_users.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error during user deletion: " . $e->getMessage();
        header("Location: admin_users.php");
        exit;
    }
}

// Fetch all users
$result = $conn->query("SELECT user_id, email, role, created_at FROM Users");
?>

<div class="container mt-4">
    <h2><i class="bi bi-people"></i> Manage Users</h2>

    <!-- Alerts -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo ucfirst($row['role']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="edit_user.php?user_id=<?php echo $row['user_id']; ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                        <a href="admin_users.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
