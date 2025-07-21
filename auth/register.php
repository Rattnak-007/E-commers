<?php
include '../config/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $address = $_POST["address"];
    $sql = "INSERT INTO users (name, email, password, address) VALUES ('$name', '$email', '$password', '$address')";
    if ($conn->query($sql) === TRUE) {
        // Auto-login after registration
        $user_id = $conn->insert_id;
        $_SESSION["user_id"] = $user_id;
        $_SESSION["user_name"] = $name;
        $_SESSION["user_email"] = $email;
        header("Location: ../User/index.php");
        exit();
    } else {
        $error = "Registration failed: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/Style.css">
</head>

<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Name" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <textarea name="address" placeholder="Address"></textarea><br><br>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>

</html>