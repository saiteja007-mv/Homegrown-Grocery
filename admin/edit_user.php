<?php
session_start();
include '../config/db.php';
include '../includes/auth.php';

// Restrict access to admins
redirect_if_not_logged_in();
restrict_to_admin();

// Check if the user ID is provided
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    $_SESSION['error'] = "No user selected for editing.";
    header("Location: admin_users.php");
    exit;
}

$user_id = intval($_GET['user_id']);

// Fetch the user's details
$stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: admin_users.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    if (empty($email) || empty($role)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Update user details
        $stmt = $conn->prepare("UPDATE Users SET email = ?, role = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $email, $role, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "User updated successfully.";
            header("Location: admin_users.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <div class="container mt-4">
        <h2>Edit User</h2>

        <!-- Alerts -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <!-- Edit Form -->
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Update User</button>
            <a href="admin_users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
