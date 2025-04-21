<?php
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();
include 'includes/header.php';
?>

<div class="container mt-4">
    <h2><i class="bi bi-person-circle"></i> Profile</h2>
    <?php
    if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email, phone, shipping_address, created_at FROM Users WHERE user_id = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo "<p><strong>Username:</strong> {$row['username']}</p>";
        echo "<p><strong>Email:</strong> {$row['email']}</p>";
        echo "<p><strong>Phone:</strong> " . ($row['phone'] ? $row['phone'] : 'Not set') . "</p>";
        echo "<p><strong>Shipping Address:</strong> " . ($row['shipping_address'] ? $row['shipping_address'] : 'Not set') . "</p>";
        echo "<p><strong>Joined:</strong> {$row['created_at']}</p>";
        echo "<a href='profile_edit.php' class='btn btn-primary mt-3'><i class='bi bi-pencil'></i> Edit Profile</a>";
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>
