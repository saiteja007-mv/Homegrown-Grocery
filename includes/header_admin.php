<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeGrown Groceries</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            padding-top: 56px; /* Adjust this value based on the navbar height */
        }
    </style>
</head>
<body>
<?php
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-success-subtle fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="../index.php">HomeGrown Groceries</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="../profile.php">Profile</a></li>
                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link" href="cart.php">-->
                    <!--        <i class="bi bi-cart"></i>-->
                    <!--        <span class="badge bg-danger"><?php echo $cart_count; ?></span>-->
                    <!--    </a>-->
                    <!--</li>-->
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="../login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
