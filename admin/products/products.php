<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../auth/login.php");
    exit();
}

include '../../config/conn.php';

$success = $error = "";

// Handle Add or Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;  // for edit
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $sale_price = empty($_POST['sale_price']) ? NULL : $_POST['sale_price'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    // Image upload handling (optional on edit)
    $image_path = null;
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_', true) . '.' . $ext;
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    if ($id) {
        // Update existing product
        if ($image_path) {
            $sql = "UPDATE products SET name=?, category_id=?, description=?, price=?, sale_price=?, image_url=?, stock=?, status=? WHERE id=?";
        } else {
            // If no new image uploaded, don't update image_url
            $sql = "UPDATE products SET name=?, category_id=?, description=?, price=?, sale_price=?, stock=?, status=? WHERE id=?";
        }
        $stmt = $conn->prepare($sql);

        if ($image_path) {
            $stmt->bind_param("sisddsssi", $name, $category_id, $description, $price, $sale_price, $image_path, $stock, $status, $id);
        } else {
            $stmt->bind_param("sisddssi", $name, $category_id, $description, $price, $sale_price, $stock, $status, $id);
        }

        if ($stmt->execute()) {
            $success = "Product updated successfully.";
        } else {
            $error = "Update failed: " . $stmt->error;
        }
    } else {
        // Insert new product
        $sql = "INSERT INTO products (name, category_id, description, price, sale_price, image_url, stock, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisddsis", $name, $category_id, $description, $price, $sale_price, $image_path, $stock, $status);

        if ($stmt->execute()) {
            $success = "Product added successfully.";
        } else {
            $error = "Insert failed: " . $stmt->error;
        }
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = $_GET['id'];
    // Optionally, delete image file here if needed

    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $success = "Product deleted successfully.";
    } else {
        $error = "Delete failed: " . $stmt->error;
    }
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Fetch all products with category name
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
") or die($conn->error);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/products-style.css" />
</head>

<body>
    <div class="admin-container">
        <?php require '../include/Navbar.php'; ?>

        <div class="container">
            <h2>Manage Products</h2>

            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <button class="btn-add" onclick="openForm()">+ Add New Product</button>

            <!-- Products Table -->
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Sale Price</th>
                        <th>Status</th>
                        <th>Stock</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr
                            data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                            data-category_id="<?= $product['category_id'] ?>"
                            data-description="<?= htmlspecialchars($product['description'], ENT_QUOTES) ?>"
                            data-price="<?= $product['price'] ?>"
                            data-sale_price="<?= $product['sale_price'] ?>"
                            data-stock="<?= $product['stock'] ?>"
                            data-status="<?= $product['status'] ?>"
                            data-image_url="<?= htmlspecialchars($product['image_url'], ENT_QUOTES) ?>">
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td>
                                <?= $product['sale_price'] !== null ? "$" . number_format($product['sale_price'], 2) : '-' ?>
                            </td>
                            <td><?= htmlspecialchars($product['status']) ?></td>
                            <td><?= $product['stock'] ?></td>
                            <td>
                                <?php if ($product['image_url']): ?>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Img" style="width:50px; height:auto; border-radius:4px;">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-edit" onclick="openForm(<?= $product['id'] ?>)">Edit</button>
                                <a href="?action=delete&id=<?= $product['id'] ?>" onclick="return confirm('Delete this product?')" class="btn-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Modal Form -->
            <div id="productFormModal" class="modal">
                <div class="modal-content">
                    <h2 id="modalTitle">Add New Product</h2>
                    <form method="POST" enctype="multipart/form-data" id="productForm">
                        <input type="hidden" name="id" id="productId" value="" />
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" required />
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php
                                // Reset pointer to start categories result
                                $categories->data_seek(0);
                                while ($cat = $categories->fetch_assoc()):
                                ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" step="0.01" id="price" name="price" required />
                        </div>

                        <div class="form-group">
                            <label for="sale_price">Sale Price ($) (optional)</label>
                            <input type="number" step="0.01" id="sale_price" name="sale_price" />
                        </div>

                        <div class="form-group">
                            <label for="image_file">Product Image <small>(Upload to replace existing)</small></label>
                            <input type="file" id="image_file" name="image_file" accept="image/*" />
                            <div id="currentImagePreview" style="margin-top:8px;"></div>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" required />
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="regular">Regular</option>
                                <option value="featured">Featured</option>
                                <option value="collection">Collection</option>
                                <option value="new_arrival">New Arrival</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="closeForm()">Cancel</button>
                            <button type="submit" class="btn-submit">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <style>
        /* ===== General ===== */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ===== Headings ===== */
        h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #222;
        }

        /* ===== Alert Messages ===== */
        .success,
        .error {
            border-radius: 6px;
            padding: 14px 20px;
            font-weight: 600;
            margin-bottom: 20px;
            max-width: 800px;
            box-shadow: 0 3px 6px rgb(0 0 0 / 0.1);
        }

        .success {
            background-color: #e6f4ea;
            color: #2e7d32;
            border: 1.5px solid #2e7d32;
        }

        .error {
            background-color: #fdecea;
            color: #b00020;
            border: 1.5px solid #b00020;
        }

        /* ===== Add New Button ===== */
        .btn-add {
            display: inline-block;
            background-color: #3b82f6;
            color: #fff;
            font-weight: 700;
            padding: 12px 28px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgb(59 130 246 / 0.3);
            user-select: none;
        }

        .btn-add:hover {
            background-color: #2563eb;
            box-shadow: 0 6px 16px rgb(37 99 235 / 0.5);
        }

        /* ===== Products Table ===== */
        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            font-size: 15px;
            box-shadow: 0 6px 20px rgb(0 0 0 / 0.07);
        }

        .products-table thead tr {
            background-color: #f3f4f6;
            border-radius: 12px;
        }

        .products-table th,
        .products-table td {
            padding: 14px 20px;
            text-align: left;
            vertical-align: middle;
            font-weight: 600;
            color: #4b5563;
        }

        .products-table thead th {
            border-bottom: none;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 13px;
        }

        .products-table tbody tr {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgb(0 0 0 / 0.05);
            transition: background-color 0.25s ease;
        }

        .products-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .products-table tbody td {
            font-weight: 500;
            color: #374151;
        }

        .products-table tbody img {
            border-radius: 6px;
            max-height: 60px;
            object-fit: contain;
        }

        /* ===== Action Buttons ===== */
        .btn-edit,
        .btn-delete {
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
            transition: background-color 0.3s ease;
            font-size: 13px;
            margin-right: 8px;
            box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
        }

        .btn-edit {
            background-color: #10b981;
            color: white;
        }

        .btn-edit:hover {
            background-color: #059669;
            box-shadow: 0 3px 8px rgb(5 150 105 / 0.4);
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #b91c1c;
            box-shadow: 0 3px 8px rgb(185 28 28 / 0.5);
        }

        /* ===== Modal Background ===== */
        .modal {
            display: none;
            /* hidden by default */
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            overflow-y: auto;
            padding: 80px 20px;
        }

        /* ===== Modal Content Box ===== */
        .modal-content {
            background-color: #fff;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px 35px;
            border-radius: 14px;
            box-shadow: 0 15px 40px rgb(0 0 0 / 0.2);
            animation: slideDownFade 0.3s ease forwards;
            position: relative;
        }

        /* Modal Title */
        .modal-content h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 28px;
            color: #111827;
            text-align: center;
        }

        /* ===== Form Styling ===== */
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
            font-size: 15px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea,
        .form-group input[type="file"] {
            font-size: 15px;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1.8px solid #d1d5db;
            transition: border-color 0.25s ease;
            font-family: inherit;
            color: #1f2937;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus,
        .form-group textarea:focus,
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 5px rgb(59 130 246 / 0.4);
        }

        textarea {
            resize: vertical;
            min-height: 70px;
        }

        /* Current image preview in form */
        #currentImagePreview img {
            max-height: 80px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 3px 8px rgb(0 0 0 / 0.1);
            margin-top: 8px;
        }

        /* ===== Form Buttons ===== */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 28px;
        }

        .btn-submit,
        .btn-cancel {
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            border: none;
            cursor: pointer;
            user-select: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 14px rgb(0 0 0 / 0.1);
        }

        .btn-submit {
            background-color: #3b82f6;
            color: white;
        }

        .btn-submit:hover {
            background-color: #2563eb;
            box-shadow: 0 6px 20px rgb(37 99 235 / 0.5);
        }

        .btn-cancel {
            background-color: #ef4444;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #b91c1c;
            box-shadow: 0 6px 20px rgb(185 28 28 / 0.5);
        }

        /* ===== Animations ===== */
        @keyframes slideDownFade {
            from {
                opacity: 0;
                transform: translateY(-25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== Responsive Adjustments ===== */
        @media (max-width: 768px) {

            .products-table th,
            .products-table td {
                padding: 12px 10px;
                font-size: 13px;
            }

            .btn-add {
                width: 100%;
                text-align: center;
                margin-bottom: 20px;
            }

            .modal-content {
                padding: 24px 20px;
                margin: 40px 10px;
            }
        }
    </style>

    <script>
        const modal = document.getElementById('productFormModal');
        const form = document.getElementById('productForm');
        const modalTitle = document.getElementById('modalTitle');

        function openForm(productId = null) {
            form.reset();
            document.getElementById('productId').value = '';
            modalTitle.textContent = 'Add New Product';
            document.getElementById('currentImagePreview').innerHTML = '';

            if (productId) {
                // Prefill form with product data
                const row = document.querySelector(`tr[data-id="${productId}"]`);
                if (!row) return;

                document.getElementById('productId').value = productId;
                document.getElementById('name').value = row.dataset.name;
                document.getElementById('category_id').value = row.dataset.category_id;
                document.getElementById('description').value = row.dataset.description;
                document.getElementById('price').value = row.dataset.price;
                document.getElementById('sale_price').value = row.dataset.sale_price;
                document.getElementById('stock').value = row.dataset.stock;
                document.getElementById('status').value = row.dataset.status;

                if (row.dataset.image_url) {
                    document.getElementById('currentImagePreview').innerHTML = `<img src="${row.dataset.image_url}" alt="Current Image" />`;
                }

                modalTitle.textContent = 'Edit Product';
            }
            modal.style.display = 'block';
        }

        function closeForm() {
            modal.style.display = 'none';
        }

        // Close modal when clicking outside content
        window.onclick = function(event) {
            if (event.target === modal) {
                closeForm();
            }
        }
    </script>
</body>

</html>