<?php
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Update user information
    $stmt = $conn->prepare("UPDATE Users SET username = ?, email = ?, phone = ?, shipping_address = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update profile. Please try again.";
    }
}

include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, phone, shipping_address FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<div class="container mt-4">
    <h2><i class="bi bi-person-gear"></i> Edit Profile</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['shipping_address'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="profile.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>