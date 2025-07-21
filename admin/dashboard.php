<?php
session_start();
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@ecommerce.com") {
    header("Location: ../login.php");
    exit();
}
// ...existing dashboard code...
