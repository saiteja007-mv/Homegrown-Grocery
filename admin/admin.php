<?php
include '../config/db.php';
include '../includes/auth.php';

// Restrict access to logged-in users and admins
redirect_if_not_logged_in();
restrict_to_admin();

include '../includes/header_admin.php';
?>

<div class="d-flex">
    <!-- sidebar_admin -->
    <?php include '../includes/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <div class="container mt-4" style="margin-left: 0px;">
        <h1 class="mb-4"><i class="bi bi-gear"></i> Admin Panel</h1>

        <div class="row">
            <div class="col-md-4">
                <a href="admin_products.php" class="btn btn-primary btn-block mb-3"><i class="bi bi-box-seam"></i> Manage Products</a>
            </div>
            <div class="col-md-4">
                <a href="admin_users.php" class="btn btn-secondary btn-block mb-3"><i class="bi bi-people"></i> Manage Users</a>
            </div>
            <div class="col-md-4">
                <a href="admin_orders.php" class="btn btn-success btn-block mb-3"><i class="bi bi-receipt"></i> Manage Orders</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
