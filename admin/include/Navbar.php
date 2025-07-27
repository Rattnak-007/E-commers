<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                <a href="../dashboard/dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="../products/products.php" <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="../categories/categories.php" <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="../orders/manage_order.php" <?= basename($_SERVER['PHP_SELF']) == 'manage_order.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="../Total_user/User.php" <?= basename($_SERVER['PHP_SELF']) == 'User.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </div>
</body>

</html>