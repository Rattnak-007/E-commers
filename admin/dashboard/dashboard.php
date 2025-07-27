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

// Chart data
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalOrderItems = $conn->query("SELECT COUNT(*) as count FROM order_items")->fetch_assoc()['count'];

// Total revenue
$totalRevenueRow = $conn->query("SELECT SUM(total_amount) as revenue FROM orders")->fetch_assoc();
$totalRevenue = $totalRevenueRow['revenue'] ? $totalRevenueRow['revenue'] : 0;

// Unique customers
$totalCustomers = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM orders")->fetch_assoc()['count'];

// Top 5 selling products (by quantity)
$topProducts = $conn->query("
    SELECT product_name, SUM(quantity) as total_qty
    FROM order_items
    GROUP BY product_name
    ORDER BY total_qty DESC
    LIMIT 5
");
$topProductNames = [];
$topProductQtys = [];
while ($row = $topProducts->fetch_assoc()) {
    $topProductNames[] = $row['product_name'];
    $topProductQtys[] = (int)$row['total_qty'];
}

// Top 5 products by revenue
$topRevenueProducts = $conn->query("
    SELECT product_name, SUM(price * quantity) as total_revenue
    FROM order_items
    GROUP BY product_name
    ORDER BY total_revenue DESC
    LIMIT 5
");
$topRevenueProductNames = [];
$topRevenueProductAmounts = [];
while ($row = $topRevenueProducts->fetch_assoc()) {
    $topRevenueProductNames[] = $row['product_name'];
    $topRevenueProductAmounts[] = (float)$row['total_revenue'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StepStyle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            <!-- Extra Order/Order Items Info Cards -->
            <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
                <div style="flex:1; min-width:220px; background:#fff; border-radius:10px; box-shadow:0 2px 8px #0001; padding:18px;">
                    <h4 style="color:#374151; font-size:15px; margin-bottom:6px;">Total Revenue</h4>
                    <div style="font-size:22px; font-weight:700; color:#16a34a;">
                        $<?= number_format($totalRevenue, 2) ?>
                    </div>
                </div>
                <div style="flex:1; min-width:220px; background:#fff; border-radius:10px; box-shadow:0 2px 8px #0001; padding:18px;">
                    <h4 style="color:#374151; font-size:15px; margin-bottom:6px;">Unique Customers</h4>
                    <div style="font-size:22px; font-weight:700; color:#2563eb;">
                        <?= $totalCustomers ?>
                    </div>
                </div>
            </div>

            <!-- Chart Reports -->
            <div style="display: flex; flex-wrap: wrap; gap: 40px; margin-bottom: 40px;">
                <div style="flex:1; min-width:320px; background:#fff; border-radius:12px; box-shadow:0 2px 8px #0001; padding:24px;">
                    <h3 style="margin-bottom:16px;">System Overview</h3>
                    <canvas id="systemBarChart" height="180"></canvas>
                </div>
                <div style="flex:1; min-width:320px; background:#fff; border-radius:12px; box-shadow:0 2px 8px #0001; padding:24px;">
                    <h3 style="margin-bottom:16px;">Top 5 Selling Products</h3>
                    <canvas id="topProductsPieChart" height="180"></canvas>
                </div>
                <div style="flex:1; min-width:320px; background:#fff; border-radius:12px; box-shadow:0 2px 8px #0001; padding:24px;">
                    <h3 style="margin-bottom:16px;">Top 5 Products by Revenue</h3>
                    <canvas id="topRevenueProductsBarChart" height="180"></canvas>
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
    <script>
        // Chart Data from PHP
        const totalProducts = <?= (int)$totalProducts ?>;
        const totalOrders = <?= (int)$totalOrders ?>;
        const totalOrderItems = <?= (int)$totalOrderItems ?>;
        const topProductNames = <?= json_encode($topProductNames) ?>;
        const topProductQtys = <?= json_encode($topProductQtys) ?>;
        const topRevenueProductNames = <?= json_encode($topRevenueProductNames) ?>;
        const topRevenueProductAmounts = <?= json_encode($topRevenueProductAmounts) ?>;

        // System Overview Bar Chart
        new Chart(document.getElementById('systemBarChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Products', 'Orders', 'Order Items'],
                datasets: [{
                    label: 'Total Count',
                    data: [totalProducts, totalOrders, totalOrderItems],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(239, 68, 68, 0.7)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Top 5 Selling Products Pie Chart
        new Chart(document.getElementById('topProductsPieChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: topProductNames,
                datasets: [{
                    data: topProductQtys,
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e42', '#ef4444', '#6366f1'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: false
                    }
                }
            }
        });

        // Top 5 Products by Revenue Bar Chart
        new Chart(document.getElementById('topRevenueProductsBarChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: topRevenueProductNames,
                datasets: [{
                    label: 'Revenue ($)',
                    data: topRevenueProductAmounts,
                    backgroundColor: [
                        '#6366f1', '#3b82f6', '#10b981', '#f59e42', '#ef4444'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>