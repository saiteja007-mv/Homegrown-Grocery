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
    $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully.";
        header("Location: admin_users.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to delete user.";
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
