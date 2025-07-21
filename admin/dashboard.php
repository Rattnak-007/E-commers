<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/conn.php';

// Fetch statistics
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE email != 'admin@ecommerce.com'")->fetch_assoc()['count'];
$featuredProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'featured'")->fetch_assoc()['count'];
$newArrivals = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'new_arrival'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StepStyle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-shoe-prints"></i>
                <span>StepStyle Admin</span>
            </div>
            <nav>
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="products.php">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="categories.php">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Dashboard</h1>
                <div class="admin-info">
                    <span>Welcome, Admin</span>
                    <img src="https://via.placeholder.com/40" alt="Admin" class="admin-avatar">
                </div>
            </header>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Products</h3>
                        <p><?= $totalProducts ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Users</h3>
                        <p><?= $totalUsers ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF9800;">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Featured Products</h3>
                        <p><?= $featuredProducts ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #E91E63;">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-details">
                        <h3>New Arrivals</h3>
                        <p><?= $newArrivals ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="recent-section">
                <h2>Recent Products</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentProducts = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
                            while ($product = $recentProducts->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td><span class="status-badge <?= $product['status'] ?>"><?= ucfirst($product['status']) ?></span></td>
                                    <td><?= $product['stock'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>

</html>