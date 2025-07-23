<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../auth/login.php");
    exit();
}
include '../../config/conn.php';

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND email != 'admin@ecommerce.com'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "User deleted successfully.";
    } else {
        $error = "Error deleting user: " . $stmt->error;
    }
}

// Fetch all users except admin
$users = $conn->query("
    SELECT id, email, created_at, 
           (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as total_orders
    FROM users 
    WHERE email != 'admin@ecommerce.com'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
        }

        .users-container {
            max-width: 1200px;
            padding: 30px;
            margin-left: 250px;
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .users-header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin: 0;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        }

        .users-table th,
        .users-table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .users-table th {
            background-color: #f1f3f5;
            color: #34495e;
            font-weight: 600;
            font-size: 15px;
        }

        .users-table tr:hover {
            background-color: #f8f9fa;
        }

        .user-email {
            font-weight: 500;
            color: #2c3e50;
        }

        .user-date {
            color: #6c757d;
            font-size: 14px;
        }

        .total-orders {
            color: #007bff;
            font-weight: 600;
        }

        .user-status {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .btn-delete {
            color: #dc3545;
            background: transparent;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .btn-delete:hover {
            color: #bd2130;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        @media (max-width: 768px) {
            .users-container {
                margin-left: 0;
                padding: 20px;
            }

            .users-table th,
            .users-table td {
                padding: 12px;
            }
        }
    </style>

</head>

<body>
    <div class="admin-container">
        <?php require '../include/Navbar.php'; ?>

        <div class="users-container">
            <div class="users-header">
                <h1>User Management</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="user-date"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td><span class="user-status status-active">Active</span></td>
                            <td>
                                <button class="btn-delete"
                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['email']); ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function deleteUser(id, email) {
            if (confirm(`Are you sure you want to delete user ${email}?`)) {
                window.location.href = `?action=delete&id=${id}`;
            }
        }
    </script>
</body>

</html>