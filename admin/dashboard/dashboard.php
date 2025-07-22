<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../auth/login.php");
    exit();
}
include '../../config/conn.php';

// Fetch statistics
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$featuredProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'featured'")->fetch_assoc()['count'];
$newArrivals = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'new_arrival'")->fetch_assoc()['count'];
$collections = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'collection'")->fetch_assoc()['count'];
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
        <?php require '../include/Navbar.php'; ?>
        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Dashboard</h1>
                <div class="admin-info">
                    <span>Welcome, Admin</span>
                    <img src="" alt="Admin" class="admin-avatar">
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
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e91ec0ff;">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Collections</h3>
                        <p><?= $collections ?></p>
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
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Sale Price</th>
                                <th>Status</th>
                                <th>Stock</th>
                                <th>Added Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentProducts = $conn->query("
                                SELECT p.*, c.name as category_name 
                                FROM products p
                                LEFT JOIN categories c ON p.category_id = c.id
                                ORDER BY p.created_at DESC 
                                LIMIT 5
                            ");
                            while ($product = $recentProducts->fetch_assoc()):
                                $status_class = strtolower($product['status']);
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="../../admin/uploads/<?= htmlspecialchars($product['image_url']) ?>"
                                                alt="<?= htmlspecialchars($product['name']) ?>"
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: #aaa;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td><?= $product['sale_price'] ? '$' . number_format($product['sale_price'], 2) : '-' ?></td>
                                    <td><span class="status-badge <?= $status_class ?>"><?= ucfirst($product['status']) ?></span></td>
                                    <td><?= $product['stock'] ?></td>
                                    <td><?= date('M d, Y', strtotime($product['created_at'])) ?></td>
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