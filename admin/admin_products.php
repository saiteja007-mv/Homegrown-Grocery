<?php
include '../config/db.php';
include '../includes/auth.php';

// Restrict access to admins
redirect_if_not_logged_in();
restrict_to_admin();
include '../includes/header_admin.php';
?>

<div class="d-flex">
    <!-- sidebar_admin_admin -->
    <?php include '../includes/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <div class="container mt-4" style="margin-left: 0px;">
        <h1 class="mb-4"><i class="bi bi-box-seam"></i> Manage Products</h1>

        <a href="add_product.php" class="btn btn-primary mb-3"><i class="bi bi-plus-circle"></i> Add Product</a>

        <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Description</th>
            <th>Old Price</th>
            <th>New Price</th>
            <th>Quantity</th>
            <th>Image</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("SELECT * FROM Products");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['product_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['description']}</td>
                    <td>\${$row['old_price']}</td>
                    <td>\${$row['new_price']}</td>
                    <td>{$row['quantity']}</td>
                    <td><img src='../assets/images/{$row['image_url']}' alt='{$row['name']}' width='50'></td>
                    <td>{$row['created_at']}</td>
                    <td>
                        <a href='edit_product.php?id={$row['product_id']}' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Edit</a>
                        <a href='delete_product.php?id={$row['product_id']}' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i> Delete</a>
                    </td>
                </tr>";
        }
        ?>
    </tbody>
</table>


    </div>
</div>

<?php include '../includes/footer.php'; ?>
