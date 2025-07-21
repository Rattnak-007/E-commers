<?php
include '../config/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password"])) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["user_name"] = $row["name"];
            $_SESSION["user_email"] = $row["email"];
            // Redirect admin to dashboard, others to User/index.html
            if ($row["email"] === "admin@ecommerce.com") {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: User/index.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/Style.css">
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>

</html>