<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../auth/login.php");
    exit();
}

include '../../config/conn.php';

// Handle Add / Edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];

    if ($_POST['action'] === 'add') {
        $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) $success = "Category added successfully.";
        else $error = "Error: " . $stmt->error;
    }

    if ($_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $sql = "UPDATE categories SET name=?, description=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $description, $id);
        if ($stmt->execute()) $success = "Category updated successfully.";
        else $error = "Update failed: " . $stmt->error;
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM categories WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $success = "Category deleted successfully.";
    else $error = "Delete failed: " . $stmt->error;
}

// Fetch categories
$categories = $conn->query("
    SELECT 
        c.*, 
        COUNT(p.id) as product_count,
        SUM(CASE WHEN p.status = 'featured' THEN 1 ELSE 0 END) as featured_count,
        SUM(CASE WHEN p.status = 'new_arrival' THEN 1 ELSE 0 END) as new_arrival_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
") or die("Query failed: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Categories</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Layout */
        .categories-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 30px;
        }

        /* Card Design */
        .category-dashboard-card {
            background-color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .category-dashboard-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        }

        /* Category Details */
        .category-details h3 {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .category-details p {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 16px;
        }

        /* Stats */
        .category-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 13px;
            color: #444;
        }

        .category-stats span {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f0f0f0;
            padding: 6px 12px;
            border-radius: 8px;
        }

        /* Buttons */
        .category-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-edit,
        .btn-delete {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.2s ease;
        }

        .btn-edit {
            color: #27ae60;
        }

        .btn-delete {
            color: #e74c3c;
        }

        .btn-edit:hover {
            background: rgba(39, 174, 96, 0.1);
        }

        .btn-delete:hover {
            background: rgba(231, 76, 60, 0.1);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: 80px auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-content h2 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Form */
        .category-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 500;
            color: #34495e;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-submit {
            background-color: #3498db;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #2980b9;
        }

        .btn-cancel {
            background-color: #e74c3c;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-cancel:hover {
            background-color: #c0392b;
        }

        /* Add Button on Top */
        .btn-add {
            background-color: #2ecc71;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-add:hover {
            background-color: #27ae60;
        }

        /* Alert Styles */
        .alert {
            margin-top: 20px;
            padding: 12px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php require '../include/Navbar.php'; ?>

        <div class="main-content">
            <header>
                <h1>Category Management</h1>
                <button class="btn-add" onclick="openAddForm()">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
            </header>

            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <!-- Categories Grid -->
            <div class="categories-dashboard-grid">
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <div class="category-dashboard-card"
                        data-id="<?= $category['id'] ?>"
                        data-name="<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($category['description'], ENT_QUOTES) ?>">
                        <div class="category-details">
                            <h3><?= htmlspecialchars($category['name']) ?></h3>
                            <p><?= htmlspecialchars($category['description']) ?></p>
                            <div class="category-stats">
                                <span><i class="fas fa-box"></i> <?= $category['product_count'] ?> Products</span>
                                <span><i class="fas fa-star"></i> <?= $category['featured_count'] ?> Featured</span>
                                <span><i class="fas fa-fire"></i> <?= $category['new_arrival_count'] ?> New</span>
                            </div>
                        </div>
                        <div class="category-actions">
                            <button class="btn-edit" onclick="editCategory(<?= $category['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-delete" onclick="deleteCategory(<?= $category['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Modal Form -->
            <div id="addCategoryForm" class="modal">
                <div class="modal-content">
                    <h2>Add New Category</h2>
                    <form method="POST" class="category-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="">

                        <div class="form-group">
                            <label>Category Name:</label>
                            <input type="text" name="name" required>
                        </div>

                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="description" required></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="closeForm()">Cancel</button>
                            <button type="submit" class="btn-submit">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        function openAddForm() {
            const modal = document.getElementById('addCategoryForm');
            modal.style.display = 'block';
            modal.querySelector('h2').textContent = "Add New Category";
            modal.querySelector('.btn-submit').textContent = "Add Category";
            modal.querySelector('input[name="action"]').value = "add";
            modal.querySelector('input[name="id"]').value = "";
            modal.querySelector('input[name="name"]').value = "";
            modal.querySelector('textarea[name="description"]').value = "";
        }

        function editCategory(id) {
            const card = document.querySelector(`[data-id='${id}']`);
            const name = card.getAttribute('data-name');
            const description = card.getAttribute('data-description');

            const modal = document.getElementById('addCategoryForm');
            modal.style.display = 'block';
            modal.querySelector('h2').textContent = "Edit Category";
            modal.querySelector('.btn-submit').textContent = "Update Category";
            modal.querySelector('input[name="action"]').value = "edit";
            modal.querySelector('input[name="id"]').value = id;
            modal.querySelector('input[name="name"]').value = name;
            modal.querySelector('textarea[name="description"]').value = description;
        }

        function closeForm() {
            document.getElementById('addCategoryForm').style.display = 'none';
        }

        function deleteCategory(id) {
            if (confirm("Are you sure you want to delete this category?")) {
                window.location.href = `?action=delete&id=${id}`;
            }
        }

        // Close modal on background click
        window.onclick = function(event) {
            const modal = document.getElementById('addCategoryForm');
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>

</html>