<?php
session_start();
include 'config/db.php';
include 'includes/auth.php';

redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // 'admin' or 'user'

// Fetch notifications
if ($user_role === 'admin') {
    // Admin sees all notifications
    $stmt = $conn->prepare("SELECT Notifications.*, Users.email FROM Notifications 
                            JOIN Users ON Notifications.user_id = Users.user_id 
                            ORDER BY created_at DESC");
} else {
    // Regular user sees their own notifications
    $stmt = $conn->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Mark all notifications as read when the page is loaded
if ($user_role === 'admin') {
    $conn->query("UPDATE Notifications SET is_read = 1");
} else {
    $update_stmt = $conn->prepare("UPDATE Notifications SET is_read = 1 WHERE user_id = ?");
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - HomeGrown</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .notification-card {
            margin-top: 20px;
        }
        .unread {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2><i class="bi bi-bell"></i> Notifications</h2>

        <?php if (!empty($notifications)): ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item list-group-item-action notification-card <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                        <h5 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small class="text-muted">
                            <?php echo $user_role === 'admin' ? 'From: ' . htmlspecialchars($notification['email']) . ' - ' : ''; ?>
                            <?php echo date('F j, Y, g:i a', strtotime($notification['created_at'])); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center mt-4">
                <h4>No notifications found!</h4>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
