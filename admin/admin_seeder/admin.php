<?php
include '../../config/conn.php';

$admin_email = "admin@ecommerce.com";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$admin_name = "Admin";

// Check if admin already exists
$sql = "SELECT * FROM users WHERE email='$admin_email'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password) VALUES ('$admin_name', '$admin_email', '$admin_password')";
    if ($conn->query($sql) === TRUE) {
        echo "Admin user seeded successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Admin user already exists.";
}

// Example: Select all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output each user
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . " - Name: " . $row["name"] . " - Email: " . $row["email"] . "<br>";
    }
} else {
    echo "No users found.";
}

$conn->close();
