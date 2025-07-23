<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../auth/login.php");
    exit();
}
include '../../config/conn.php';

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin - Manage Orders</title>
    <link rel="stylesheet" href="../assets/css/admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .orders-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
            margin-bottom: 30px;
        }

        .orders-table th,
        .orders-table td {
            padding: 14px 16px;
            text-align: left;
            font-size: 15px;
            color: #374151;
            border-bottom: 1px solid #e9ecef;
        }

        .orders-table th {
            background: #f3f4f6;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .orders-table tbody tr {
            background: #fff;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .orders-table tbody tr:hover {
            background: #f9fafb;
        }

        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            background: #f9f9f9;
            border-radius: 6px;
            font-size: 14px;
        }

        .order-items-table th,
        .order-items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }

        .order-items-table th {
            background: #f1f3f5;
            font-weight: 600;
        }

        .order-items-table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php require '../include/Navbar.php'; ?>
        <div class="main-content">
            <h2>Order Management</h2>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Items</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= $order['user_id'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                            <td><?= htmlspecialchars($order['address']) ?></td>
                            <td>$<?= number_format($order['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= $order['created_at'] ?></td>
                            <td>
                                <table class="order-items-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $items = $conn->query("SELECT * FROM order_items WHERE order_id=" . intval($order['id']));
                                        while ($item = $items->fetch_assoc()):
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                <td>$<?= number_format($item['price'], 2) ?></td>
                                                <td><?= $item['quantity'] ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>