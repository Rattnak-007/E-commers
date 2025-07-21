<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/conn.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $sale_price = empty($_POST['sale_price']) ? NULL : $_POST['sale_price'];
    $image_url = $_POST['image_url'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    $sql = "INSERT INTO products (name, category_id, description, price, sale_price, image_url, stock, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisddsis", $name, $category_id, $description, $price, $sale_price, $image_url, $stock, $status);

    if ($stmt->execute()) {
        $success = "Product added successfully";
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<style>
    /* assets/css/admin.css */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        margin: 0;
        padding: 20px;
        color: #333;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
        padding: 30px;
    }

    h2 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        margin-top: 30px;
    }

    /* Form Styles */
    .product-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 20px;
        background: #f8fafc;
        padding: 25px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2d3748;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #cbd5e0;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s;
        background-color: white;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    button[type="submit"] {
        grid-column: span 2;
        background: #3498db;
        color: white;
        border: none;
        padding: 14px;
        font-size: 18px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s;
        font-weight: 600;
        max-width: 300px;
        margin: 10px auto;
    }

    button[type="submit"]:hover {
        background: #2980b9;
    }

    /* Table Styles */
    .products-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .products-table th {
        background: #3498db;
        color: white;
        text-align: left;
        padding: 15px;
        font-weight: 600;
    }

    .products-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .products-table tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .products-table tr:hover {
        background-color: #edf2f7;
    }

    /* Button Styles */
    .btn-edit,
    .btn-delete {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s;
        margin-right: 5px;
    }

    .btn-edit {
        background: #f39c12;
        color: white;
        border: 1px solid #e67e22;
    }

    .btn-edit:hover {
        background: #e67e22;
    }

    .btn-delete {
        background: #e74c3c;
        color: white;
        border: 1px solid #c0392b;
    }

    .btn-delete:hover {
        background: #c0392b;
    }

    /* Message Styles */
    .success {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
        border-left: 5px solid #28a745;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
        border-left: 5px solid #dc3545;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .product-form {
            grid-template-columns: 1fr;
        }

        button[type="submit"] {
            grid-column: span 1;
        }

        .products-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>

<body>
    <div class="container">
        <h2>Add New Product</h2>
        <?php
        if (isset($success)) echo "<p class='success'>$success</p>";
        if (isset($error)) echo "<p class='error'>$error</p>";
        ?>

        <form method="POST" class="product-form">
            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Category:</label>
                <select name="category_id" required>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>Price:</label>
                <input type="number" step="0.01" name="price" required>
            </div>

            <div class="form-group">
                <label>Sale Price (optional):</label>
                <input type="number" step="0.01" name="sale_price">
            </div>

            <div class="form-group">
                <label>Image URL:</label>
                <input type="url" name="image_url" required>
            </div>

            <div class="form-group">
                <label>Stock:</label>
                <input type="number" name="stock" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <select name="status" required>
                    <option value="regular">Regular</option>
                    <option value="featured">Featured</option>
                    <option value="collection">Collection</option>
                    <option value="new_arrival">New Arrival</option>
                </select>
            </div>

            <button type="submit">Add Product</button>
        </form>

        <!-- Display existing products -->
        <h2>Existing Products</h2>
        <table class="products-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
                while ($product = $products->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td><?= $product['status'] ?></td>
                        <td><?= $product['stock'] ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn-edit">Edit</a>
                            <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>