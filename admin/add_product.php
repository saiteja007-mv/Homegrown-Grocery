<?php
include '../config/db.php';
include '../includes/auth.php';

redirect_if_not_logged_in();
restrict_to_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $category = htmlspecialchars($_POST['category']);
    $description = htmlspecialchars($_POST['description']);
    $old_price = htmlspecialchars($_POST['old_price']);
    $new_price = htmlspecialchars($_POST['new_price']);
    $quantity = htmlspecialchars($_POST['quantity']);

    // Handle image upload
    $image = $_FILES['image']['name'];
    $target_dir = "../assets/images/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO Products (name, category, description, old_price, new_price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddss", $name, $category, $description, $old_price, $new_price, $quantity, $image);

        if ($stmt->execute()) {
            header('Location: admin_products.php');
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Failed to upload image.";
    }
}

include '../includes/header_admin.php';
?>

<div class="container mt-4" style="margin-left: 250px;">
    <h1 class="mb-4"><i class="bi bi-plus-circle"></i> Add Product</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-control" id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="Fruits">Fruits</option>
                <option value="Vegetables">Vegetables</option>
                <option value="Drinks">Drinks</option>
                <option value="Spices">Spices</option>
                <option value="Poultry">Poultry</option>
                <option value="Meat">Meat</option>
                <option value="Dairy">Dairy</option>
                <option value="Others">Others</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="old_price" class="form-label">Old Price</label>
            <input type="number" step="0.01" class="form-control" id="old_price" name="old_price">
        </div>
        <div class="mb-3">
            <label for="new_price" class="form-label">New Price</label>
            <input type="number" step="0.01" class="form-control" id="new_price" name="new_price" required>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <input type="file" class="form-control" id="image" name="image" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>