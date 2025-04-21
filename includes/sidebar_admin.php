<div class="d-flex flex-column flex-shrink-0 p-3 bg-success-subtle" style="width: 250px; height: 100vh;">
    <a href="../index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
        <span class="fs-4"><i class="bi bi-basket"></i> HomeGrown</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="../index.php" class="nav-link link-dark">
                <i class="bi bi-house-door"></i> Home
            </a>
        </li>
        <li>
            <a href="../orders.php" class="nav-link link-dark">
                <i class="bi bi-bag-check"></i> Orders
            </a>
        </li>
        <li>
            <a href="../cart.php" class="nav-link link-dark">
                <i class="bi bi-cart"></i> Cart
            </a>
        </li>
        <li>
            <a href="../profile.php" class="nav-link link-dark">
                <i class="bi bi-person"></i> Account
            </a>
        </li>
        <li>
            <a href="../index.php" class="nav-link link-dark">
                <i class="bi bi-question-circle"></i> Help
            </a>
        </li>
        <li>
            <a href="../index.php" class="nav-link link-dark">
                <i class="bi bi-bell"></i> Notifications
            </a>
        </li>

        <!-- Admin Links -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <hr>
            <li>
                <a href="../admin/admin_products.php" class="nav-link link-dark">
                    <i class="bi bi-box-seam"></i> Manage Products
                </a>
            </li>
            <li>
                <a href="../admin/admin_users.php" class="nav-link link-dark">
                    <i class="bi bi-people"></i> Manage Users
                </a>
            </li>
            <li>
                <a href="../admin/admin_orders.php" class="nav-link link-dark">
                    <i class="bi bi-receipt"></i> Manage Orders
                </a>
            </li>
            <li>
                <a href="../admin/admin_help.php" class="nav-link link-dark">
                    <i class="bi bi-question-circle"></i> Help Tickets
                </a>
            </li>
        <?php endif; ?>

        <hr>
        <li>
            <a href="../logout.php" class="nav-link link-dark">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>
</div>
