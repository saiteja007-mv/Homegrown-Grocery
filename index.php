<?php include 'config/db.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="d-flex">
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="container mt-4 mb-4">
        <div class="jumbotron text-center jumbotron-bg">
            <h1 class="h1 text-danger bold">Welcome to HomeGrown!</h1>
            <p class="lead text-danger">Fresh groceries delivered to your door.</p>
        </div>

        <!-- Search and Filters -->
        <form method="GET" class="row g-3 mt-2">
            <div class="col-12 col-md-6 col-lg-3">
                <input type="text" name="search" class="form-control" placeholder="Search by name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <option value="Fruits" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Fruits') ? 'selected' : ''; ?>>Fruits</option>
                    <option value="Vegetables" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Vegetables') ? 'selected' : ''; ?>>Vegetables</option>
                    <option value="Drinks" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Drinks') ? 'selected' : ''; ?>>Drinks</option>
                    <option value="Spices" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Spices') ? 'selected' : ''; ?>>Spices</option>
                    <option value="Poultry" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Poultry') ? 'selected' : ''; ?>>Poultry</option>
                    <option value="Meat" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Meat') ? 'selected' : ''; ?>>Meat</option>
                    <option value="Dairy" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Dairy') ? 'selected' : ''; ?>>Dairy</option>
                    <option value="Others" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Others') ? 'selected' : ''; ?>>Others</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <input type="number" name="min_price" class="form-control" placeholder="Min Price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <input type="number" name="max_price" class="form-control" placeholder="Max Price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
            </div>
            <div class="col-12 col-md-6 col-lg-3 mt-2">
                <input type="date" name="date" class="form-control" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
            </div>
            <div class="col-12 col-md-6 col-lg-3 mt-2">
                <button type="submit" class="btn btn-success w-100">Filter</button>
            </div>
        </form>

        <!-- Products -->
        <div class="row mt-4">
            <?php
            // Initialize query and parameters
            $query = "SELECT * FROM Products WHERE 1=1";
            $params = [];
            $types = '';

            // Add search filter
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $query .= " AND name LIKE ?";
                $params[] = '%' . $_GET['search'] . '%';
                $types .= 's';
            }

            // Add category filter
            if (isset($_GET['category']) && !empty($_GET['category'])) {
                $query .= " AND category = ?";
                $params[] = $_GET['category'];
                $types .= 's';
            }

            // Add price range filter
            if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                $query .= " AND new_price >= ?";
                $params[] = $_GET['min_price'];
                $types .= 'd';
            }
            if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                $query .= " AND new_price <= ?";
                $params[] = $_GET['max_price'];
                $types .= 'd';
            }

            // Add date filter
            if (isset($_GET['date']) && !empty($_GET['date'])) {
                $query .= " AND DATE(created_at) = ?";
                $params[] = $_GET['date'];
                $types .= 's';
            }

            // Prepare and execute the query
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            // Display products
            while ($row = $result->fetch_assoc()) {
                // Calculate percentage change
                $percent_change = $row['old_price'] > 0 ? round((($row['new_price'] - $row['old_price']) / $row['old_price']) * 100) : 0;
                $percent_text = $percent_change < 0
                    ? '<span style="color: green; font-size: small;">' . $percent_change . '%</span>'
                    : '<span style="color: red; font-size: small;">+' . $percent_change . '%</span>';

                echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100">
                            <img src="assets/images/' . $row['image_url'] . '" class="card-img-top img-fluid product-image" alt="' . $row['name'] . '">
                            <div class="card-body">
                                <h5 class="card-title">' . $row['name'] . '</h5>
                                <p class="card-text">
                                    <span style="text-decoration: line-through; color: red;">$' . $row['old_price'] . '</span>
                                    <span style="font-weight: bold; color: green;">$' . $row['new_price'] . '</span>
                                    ' . $percent_text . '
                                </p>
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#productModal' . $row['product_id'] . '">View</button>
                                <a href="add_to_cart.php?add=' . $row['product_id'] . '" class="btn btn-success">
                                    <i class="bi bi-cart"></i>
                                </a>
                            </div>
                        </div>
                    </div>';
            }
            ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
