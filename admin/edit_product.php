<?php
include '../config/db.php';
include '../includes/auth.php';

redirect_if_not_logged_in();
restrict_to_admin();

// Fetch product details
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM Products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// Update product details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $category = htmlspecialchars($_POST['category']);
    $description = htmlspecialchars($_POST['description']);
    $old_price = htmlspecialchars($_POST['old_price']);
    $new_price = htmlspecialchars($_POST['new_price']);
    $quantity = htmlspecialchars($_POST['quantity']);

    // Handle image upload
    $image = $product['image_url']; // Default to current image
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target_dir = "../assets/images/";
        $target_file = $target_dir . basename($image);

        // Move the uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            echo "Failed to upload image.";
            exit;
        }
    }

    $stmt = $conn->prepare("UPDATE Products SET name = ?, category = ?, description = ?, old_price = ?, new_price = ?, quantity = ?, image_url = ? WHERE product_id = ?");
    $stmt->bind_param("sssddisi", $name, $category, $description, $old_price, $new_price, $quantity, $image, $id);

    if ($stmt->execute()) {
        header('Location: admin_products.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

include '../includes/header_admin.php';
?>

<div class="container mt-4" style="margin-left: 250px;">
    <h1 class="mb-4"><i class="bi bi-pencil"></i> Edit Product</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo $product['name']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-control" id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="Fruits" <?php echo $product['category'] == 'Fruits' ? 'selected' : ''; ?>>Fruits</option>
                <option value="Vegetables" <?php echo $product['category'] == 'Vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                <option value="Drinks" <?php echo $product['category'] == 'Drinks' ? 'selected' : ''; ?>>Drinks</option>
                <option value="Spices" <?php echo $product['category'] == 'Spices' ? 'selected' : ''; ?>>Spices</option>
                <option value="Poultry" <?php echo $product['category'] == 'Poultry' ? 'selected' : ''; ?>>Poultry</option>
                <option value="Meat" <?php echo $product['category'] == 'Meat' ? 'selected' : ''; ?>>Meat</option>
                <option value="Dairy" <?php echo $product['category'] == 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                <option value="Others" <?php echo $product['category'] == 'Others' ? 'selected' : ''; ?>>Others</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $product['description']; ?></textarea>
        </div>
        <div class="mb-3">
            <label for="old_price" class="form-label">Old Price</label>
            <input type="number" step="0.01" class="form-control" id="old_price" name="old_price" value="<?php echo $product['old_price']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="new_price" class="form-label">New Price</label>
            <input type="number" step="0.01" class="form-control" id="new_price" name="new_price" value="<?php echo $product['new_price']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $product['quantity']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <input type="file" class="form-control" id="image" name="image">
            <p>Current Image:</p>
            <img src="../assets/images/<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" width="100">
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
