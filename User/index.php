<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/conn.php';

// Initialize messages
$success_message = '';
$error_message = '';

// Show success message if redirected after order
if (isset($_GET['order']) && $_GET['order'] === 'success') {
    $success_message = "Order placed successfully!";
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_submit'])) {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['checkoutName']);
    $email = trim($_POST['checkoutEmail']);
    $address = trim($_POST['checkoutAddress']);
    $cart_json = isset($_POST['cart_data']) ? $_POST['cart_data'] : '';
    $cart = [];
    $total = 0;

    // Validate fields
    if (!$name || !$email || !$address) {
        $error_message = "Please fill in all checkout fields.";
    } elseif (empty($cart_json)) {
        $error_message = "Your cart is empty.";
    } else {
        $cart = json_decode($cart_json, true);
        if (!is_array($cart) || count($cart) == 0) {
            $error_message = "Your cart is empty.";
        }
    }

    if (!$error_message) {
        foreach ($cart as $item) {
            $total += floatval($item['price']) * intval($item['qty']);
        }

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, address, total_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        if ($stmt) {
            $stmt->bind_param("isssd", $user_id, $name, $email, $address, $total);
            if ($stmt->execute()) {
                $order_id = $conn->insert_id;

                // Insert order items
                $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
                if ($stmt_item) {
                    foreach ($cart as $item) {
                        $product_name = $item['name'];
                        $price = floatval($item['price']);
                        $qty = intval($item['qty']);
                        $stmt_item->bind_param("isdi", $order_id, $product_name, $price, $qty);
                        if (!$stmt_item->execute()) {
                            $error_message = "Failed to insert order item: " . $stmt_item->error;
                            break;
                        }
                    }
                    if (!$error_message) {
                        // Redirect to avoid resubmission
                        header("Location: index.php?order=success");
                        exit();
                    }
                } else {
                    $error_message = "Failed to prepare order items statement: " . $conn->error;
                }
            } else {
                $error_message = "Failed to insert order: " . $stmt->error;
            }
        } else {
            $error_message = "Failed to prepare order statement: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StepStyle | Premium Footwear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary: #4a6de5;
            --primary-dark: #3a5bc7;
            --secondary: #ff6b6b;
            --dark: #2d3748;
            --light: #f8f9fa;
            --gray: #718096;
            --light-gray: #e2e8f0;
            --success: #48bb78;
            --danger: #e53e3e;
            --warning: #ed8936;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo i {
            color: var(--primary);
        }

        .logo span {
            color: var(--primary);
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        nav ul li a:hover {
            color: var(--primary);
        }

        .nav-icons {
            display: flex;
            gap: 20px;
        }

        .nav-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--dark);
            transition: color 0.3s;
        }

        .nav-icon:hover {
            color: var(--primary);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--secondary);
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .mobile-menu {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Slideshow Section */
        .slideshow {
            position: relative;
            width: 100%;
            margin: 20px auto;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            height: 650px;
        }

        .slideshow-container {
            position: relative;
            height: 100%;
        }

        .slide-image {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 10s ease;
        }

        .slide.active .slide-image {
            transform: scale(1.05);
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 30px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
            color: white;
            transform: translateY(100px);
            transition: transform 0.5s ease-out;
        }

        .slide.active .slide-content {
            transform: translateY(0);
        }

        .slide h2 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .slide p {
            font-size: 1.2rem;
            max-width: 600px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .navigation {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            transform: translateY(-50%);
        }

        .prev,
        .next {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .prev:hover,
        .next:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .dots-container {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 12px;
            z-index: 10;
        }

        .dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            background: white;
            transform: scale(1.2);
        }

        .dot:hover {
            background: white;
            transform: scale(1.3);
        }

        .progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 5px;
            background: linear-gradient(to right, #ff8a00, #da1b60);
            width: 0%;
            transition: width 5s linear;
            z-index: 20;
        }

        .slide.active .progress-bar {
            width: 100%;
        }

        .info-panel {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            color: white;
        }

        .info-panel h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }


        /* Section Styles */
        section {
            padding: 60px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2rem;
            color: var(--dark);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        /* Products Grid */
        .products-grid,
        .arrivals-grid,
        .features-grid,
        .testimonials-grid,
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--secondary);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .product-image {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-actions {
            position: absolute;
            bottom: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s;
        }

        .product-card:hover .product-actions {
            opacity: 1;
            transform: translateY(0);
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .current-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .original-price {
            font-size: 0.9rem;
            color: var(--gray);
            text-decoration: line-through;
        }

        .product-card p {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--gray);
        }

        .rating {
            color: #f59e0b;
        }

        /* Features Section */
        .features-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .feature-card {
            background: white;
            padding: 30px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 12px;
            font-size: 1.2rem;
        }

        .feature-card p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* Testimonials */
        .testimonial-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
        }

        .testimonial-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .testimonial-rating {
            color: #f59e0b;
        }

        .testimonial-content {
            color: var(--gray);
            font-style: italic;
            line-height: 1.7;
        }

        /* Newsletter */
        .newsletter {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 60px 0;
        }

        .newsletter h2 {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .newsletter p {
            max-width: 600px;
            margin: 0 auto 30px;
            font-size: 1.1rem;
        }

        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
        }

        .newsletter-form input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 50px 0 0 50px;
            font-size: 1rem;
        }

        .newsletter-form button {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 0 50px 50px 0;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .newsletter-form button:hover {
            background: #ff5252;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 0;
        }

        .footer-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .footer-col h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
        }

        .footer-col p {
            color: var(--light-gray);
            margin-bottom: 20px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .social-icon:hover {
            background: var(--primary);
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul li a {
            color: var(--light-gray);
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-col ul li a:hover {
            color: var(--primary);
        }

        .copyright {
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--light-gray);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
            z-index: 10000;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* Cart Modal */
        .cart-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .cart-modal.show {
            display: flex;
        }

        .cart-modal-content {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 600px;
            padding: 30px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .cart-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.3s;
        }

        .cart-modal-close:hover {
            color: var(--dark);
        }

        #cartForm {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        #cartForm input,
        #cartForm textarea {
            width: 100%;
            padding: 12px 16px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: #fff;
            transition: border-color 0.2s ease;
        }

        #cartForm input:focus,
        #cartForm textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 109, 229, 0.1);
        }

        #cartForm button[type="submit"] {
            background: var(--primary);
            color: #fff;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 15px;
        }

        #cartForm button[type="submit"]:hover {
            background: var(--primary-dark);
        }

        /* Cart table styles */
        #cartItemsTableWrapper {
            overflow-x: auto;
            margin: 20px 0;
        }

        #cartItems {
            width: 100%;
            border-collapse: collapse;
        }

        #cartItems th {
            text-align: left;
            padding: 12px;
            background: #f5f7fb;
            color: var(--dark);
            font-weight: 600;
        }

        #cartItems td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        #cartSummary {
            padding: 15px;
            background: #f5f7fb;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: right;
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {

            .products-grid,
            .arrivals-grid,
            .features-grid,
            .testimonials-grid,
            .footer-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .slideshow {
                height: 400px;
            }
        }

        @media (max-width: 900px) {
            nav ul {
                display: none;
                position: absolute;
                top: 80px;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                gap: 0;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }

            nav ul.show {
                display: flex;
            }

            nav ul li {
                width: 100%;
                border-bottom: 1px solid #eee;
            }

            nav ul li a {
                padding: 15px 20px;
                display: block;
            }

            .mobile-menu {
                display: block;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {

            .products-grid,
            .arrivals-grid,
            .testimonials-grid,
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .slideshow {
                height: 400px;
            }

            .slide h2 {
                font-size: 2rem;
            }

            .prev,
            .next {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }

            .header h1 {
                font-size: 2.2rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .newsletter-form {
                flex-direction: column;
                gap: 10px;
            }

            .newsletter-form input,
            .newsletter-form button {
                width: 100%;
                border-radius: 50px;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {

            .products-grid,
            .arrivals-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .features-grid,
            .footer-grid {
                grid-template-columns: 1fr;
            }

            .slideshow {
                height: 300px;
            }

            .header-container {
                flex-wrap: wrap;
            }

            .logo {
                font-size: 1.5rem;
            }

            .nav-icons {
                gap: 15px;
            }

            .section {
                padding: 40px 0;
            }

            .section-title {
                font-size: 1.6rem;
                margin-bottom: 30px;
            }

            .caption {
                left: 20px;
                bottom: 20px;
                padding: 15px;
                max-width: 90%;
            }

            .caption p {
                font-size: 1rem;
            }

            .prev,
            .next {
                font-size: 1.5rem;
                padding: 8px 12px;
            }
        }

        @media (max-width: 480px) {
            .slideshow {
                height: 300px;
            }

            .slide h2 {
                font-size: 1.6rem;
            }

            .slide p {
                font-size: 1rem;
            }

            .slide-content {
                padding: 20px;
            }

            .prev,
            .next {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 1.4rem;
            }

            .product-actions {
                opacity: 1;
                transform: translateY(0);
            }

            .cart-modal-content {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="#" id="home" class="logo"><i class="fas fa-shoe-prints"></i>Step<span>Style</span></a>
            <nav>
                <ul id="navMenu">
                    <li>
                        <a href="#home" id="home"><i class="fas fa-home"></i>Home</a>
                    </li>
                    <li>
                        <a href="#featured" id="shopLink"><i class="fas fa-store"></i>Shop</a>
                    </li>
                    <li>
                        <a href="#new-arrivals"><i class="fas fa-fire"></i>New Arrivals</a>
                    </li>
                    <li>
                        <a href="#collections"><i class="fas fa-box-open"></i>Collections</a>
                    </li>
                    <li>
                        <a href="#contact"><i class="fas fa-phone"></i>Contact</a>
                    </li>
                </ul>
            </nav>
            <div class="nav-icons">
                <div class="nav-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="nav-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="nav-icon cart-icon" id="cartIcon">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
                <a href="../auth/logout.php" class="nav-icon" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
            <div class="mobile-menu" id="mobileMenu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Slideshow Section -->

    <section class="container slideshow">
        <div class="slideshow-container">
            <!-- Slide 1 -->
            <div class="slide active">
                <img src="https://i.pinimg.com/1200x/e7/1f/48/e71f480bd75e40b75dfb3b18e5611f45.jpg"
                    alt="Retro High-Tops" class="slide-image">
                <div class="progress-bar"></div>
                <div class="slide-content">
                    <h2>Retro High-Tops</h2>
                    <p>Classic style, modern comfort. Limited edition now available.</p>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="slide">
                <img src="https://i.pinimg.com/1200x/1d/05/89/1d05891a227b694a056aa268ae67f6a5.jpg"
                    alt="Designer Loafers" class="slide-image">
                <div class="progress-bar"></div>
                <div class="slide-content">
                    <h2>Designer Loafers</h2>
                    <p>Handcrafted luxury for every occasion. Shop exclusive designs.</p>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="slide">
                <img src="https://i.pinimg.com/1200x/3d/cf/d1/3dcfd156575f37ce12cd5d5bf02065e1.jpg"
                    alt="Street Sneakers" class="slide-image">
                <div class="progress-bar"></div>
                <div class="slide-content">
                    <h2>Street Sneakers</h2>
                    <p>Urban look for everyday wear. Trending styles for you.</p>
                </div>
            </div>

            <!-- Slide 4 -->
            <div class="slide">
                <img src="https://i.pinimg.com/1200x/aa/b4/5a/aab45a26d24ea57310206b52c61f132f.jpg"
                    alt="UltraBoost Runners" class="slide-image">
                <div class="progress-bar"></div>
                <div class="slide-content">
                    <h2>UltraBoost Runners</h2>
                    <p>Experience maximum comfort with our latest running technology.</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="navigation">
                <div class="prev" id="slidePrev">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="next" id="slideNext">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <!-- Dots -->
            <div class="dots-container" id="slideDots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </section>
    <!-- Featured Products -->
    <section id="featured" class="featured-products">
        <div class="container">
            <h2 class="section-title">Featured Shoes</h2>
            <div class="products-grid">
                <?php
                $featured = $conn->query("SELECT * FROM products WHERE status='featured' LIMIT 4");
                while ($product = $featured->fetch_assoc()):
                ?>
                    <div class="product-card">
                        <div class="product-badge">Featured</div>
                        <div class="product-image">
                            <img src="<?= !empty($product['image_url']) ? '../admin/uploads/' . $product['image_url'] : '../assets/images/default-product.jpg' ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>" />
                            <div class="product-actions">
                                <div class="action-btn"><i class="fas fa-heart"></i></div>
                                <div class="action-btn"><i class="fas fa-eye"></i></div>
                                <div class="action-btn add-to-cart"
                                    data-id="<?= $product['id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-price="<?= $product['price'] ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">
                                <span class="current-price">$<?= number_format($product['price'], 2) ?></span>
                                <?php if ($product['sale_price']): ?>
                                    <span class="original-price">$<?= number_format($product['sale_price'], 2) ?></span>
                                <?php endif; ?>
                            </div>
                            <p><?= htmlspecialchars($product['description']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Collections Section -->
    <section id="collections" class="collections-section">
        <div class="container">
            <h2 class="section-title">Collections</h2>
            <div class="products-grid">
                <?php
                $collections = $conn->query("SELECT * FROM products WHERE status='collection' LIMIT 4");
                while ($product = $collections->fetch_assoc()):
                ?>
                    <div class="product-card">
                        <div class="product-badge">Limited</div>
                        <div class="product-image">
                            <img src="<?= !empty($product['image_url']) ? '../admin/uploads/' . $product['image_url'] : '../assets/images/default-product.jpg' ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>" />
                            <div class="product-actions">
                                <div class="action-btn"><i class="fas fa-heart"></i></div>
                                <div class="action-btn"><i class="fas fa-eye"></i></div>
                                <div class="action-btn add-to-cart"
                                    data-id="<?= $product['id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-price="<?= $product['price'] ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">
                                <span class="current-price">$<?= number_format($product['price'], 2) ?></span>
                            </div>
                            <p><?= htmlspecialchars($product['description']) ?></p>
                            <div class="product-meta">
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    <i class="far fa-star"></i>
                                    (<?= rand(10, 50) ?>)
                                </div>
                                <div>Limited stock</div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="new-arrivals" id="new-arrivals">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <div class="arrivals-grid">
                <?php
                $new_arrivals = $conn->query("SELECT * FROM products WHERE status='new_arrival' LIMIT 4");
                while ($product = $new_arrivals->fetch_assoc()):
                ?>
                    <div class="product-card">
                        <div class="product-badge">New</div>
                        <div class="product-image">
                            <img src="<?= !empty($product['image_url']) ? '../admin/uploads/' . $product['image_url'] : '../assets/images/default-product.jpg' ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>" />
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">
                                <span class="current-price">$<?= number_format($product['price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose StepStyle</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-truck"></i>
                    <h3>Free Shipping</h3>
                    <p>Free worldwide shipping on all orders over $100</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-undo"></i>
                    <h3>30-Day Returns</h3>
                    <p>Not satisfied? Return within 30 days for a full refund</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>2-Year Warranty</h3>
                    <p>All products come with a 2-year manufacturer warranty</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our customer service team is always ready to help</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img
                                src="https://randomuser.me/api/portraits/women/43.jpg"
                                alt="Sarah Johnson" />
                        </div>
                        <div>
                            <h3>Sarah Johnson</h3>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        "The UltraBoost Runners are the most comfortable shoes I've ever
                        worn. Perfect for my daily runs and gym sessions. Will definitely
                        buy again!"
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img
                                src="https://randomuser.me/api/portraits/men/32.jpg"
                                alt="Michael Chen" />
                        </div>
                        <div>
                            <h3>Michael Chen</h3>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        "The customization options are fantastic! I was able to create the
                        perfect pair of Oxfords for my wedding. Great quality and
                        service."
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img
                                src="https://randomuser.me/api/portraits/women/68.jpg"
                                alt="Emily Rodriguez" />
                        </div>
                        <div>
                            <h3>Emily Rodriguez</h3>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        "I bought the TrailMaster boots for my hiking trip to the Rockies.
                        They performed exceptionally well in all conditions. Highly
                        recommended!"
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <h2>Join Our Newsletter</h2>
            <p>
                Subscribe to get exclusive offers, new product announcements, and
                style inspiration
            </p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address" />
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>StepStyle</h3>
                    <p>
                        Premium footwear for every occasion. Quality, comfort, and style
                        in every step.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Shop</h3>
                    <ul>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>Men's Collection</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>Women's Collection</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>New Arrivals</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>Best Sellers</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>Special Offers</a>
                        </li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3>Information</h3>
                    <ul>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>About Us</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>Contact Us</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>Shipping Policy</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>Returns & Exchanges</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-chevron-right"></i>FAQs</a>
                        </li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul>
                        <li>
                            <a href="#"><i class="fas fa-map-marker-alt"></i>123 Fashion Street, New
                                York, NY</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-phone"></i>+1 (555) 123-4567</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-envelope"></i>info@stepstyle.com</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-clock"></i>Mon-Fri: 9AM - 8PM</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-clock"></i>Sat-Sun: 10AM - 6PM</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2023 StepStyle. All rights reserved.</p>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Item added to cart!</span>
    </div>

    <!-- Cart Modal -->
    <div class="cart-modal" id="cartModal">
        <div class="cart-modal-content">
            <span class="cart-modal-close" id="cartModalClose">&times;</span>
            <h2>Your Cart</h2>
            <?php if (!empty($success_message)): ?>
                <div style="color:green; margin-bottom:10px;"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div style="color:red; margin-bottom:10px;"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <!-- Cart Info Summary -->
            <div id="cartInfoSummary" style="margin-bottom: 16px; font-size: 16px; color: #333;"></div>

            <!-- Order Items Table -->
            <div id="cartItemsTableWrapper">
                <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:12px; background:#f5f7fb;">Product</th>
                            <th style="text-align:left; padding:12px; background:#f5f7fb;">Price</th>
                            <th style="text-align:left; padding:12px; background:#f5f7fb;">Qty</th>
                            <th style="text-align:left; padding:12px; background:#f5f7fb;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="cartItems">
                        <!-- JS will fill this -->
                    </tbody>
                </table>
            </div>
            <div id="cartSummary" style="padding:15px; background:#f5f7fb; border-radius:8px; font-size:1.1rem; font-weight:600; text-align:right;"></div>

            <!-- Checkout Form -->
            <form id="cartForm" method="POST" autocomplete="off">
                <div class="form-group">
                    <input type="text" name="checkoutNameVisible" id="checkoutNameVisible" placeholder="Full Name" required />
                </div>
                <div class="form-group">
                    <input type="email" name="checkoutEmailVisible" id="checkoutEmailVisible" placeholder="Email Address" required />
                </div>
                <div class="form-group">
                    <textarea name="checkoutAddressVisible" id="checkoutAddressVisible" placeholder="Shipping Address" required></textarea>
                </div>
                <!-- Hidden fields for PHP submission -->
                <input type="hidden" name="checkoutName" id="checkoutName" />
                <input type="hidden" name="checkoutEmail" id="checkoutEmail" />
                <input type="hidden" name="checkoutAddress" id="checkoutAddress" />
                <input type="hidden" name="cart_data" id="cartDataInput" />
                <input type="hidden" name="checkout_submit" value="1" />
                <button type="submit" class="btn btn-secondary" style="width: 100%">Checkout</button>
            </form>
        </div>
    </div>

    <!-- Main JS -->
    <script>
        // Toggle mobile menu
        document.getElementById('mobileMenu').addEventListener('click', function() {
            document.getElementById('navMenu').classList.toggle('show');
        });

        // Slideshow functionality
        let slideIndex = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');

        function showSlide(n) {
            if (n >= slides.length) slideIndex = 0;
            if (n < 0) slideIndex = slides.length - 1;

            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            slides[slideIndex].classList.add('active');
            dots[slideIndex].classList.add('active');
        }

        document.getElementById('slideNext').addEventListener('click', () => {
            slideIndex++;
            showSlide(slideIndex);
        });

        document.getElementById('slidePrev').addEventListener('click', () => {
            slideIndex--;
            showSlide(slideIndex);
        });

        // Auto advance slides
        setInterval(() => {
            slideIndex++;
            showSlide(slideIndex);
        }, 5000);

        // Cart functionality
        let cart = [];
        const cartIcon = document.getElementById('cartIcon');
        const cartModal = document.getElementById('cartModal');
        const cartModalClose = document.getElementById('cartModalClose');
        const cartCount = document.getElementById('cartCount');
        const cartItems = document.getElementById('cartItems');
        const cartSummary = document.getElementById('cartSummary');
        const cartInfoSummary = document.getElementById('cartInfoSummary');
        const toast = document.getElementById('toast');

        // Add to cart buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = parseFloat(this.getAttribute('data-price'));

                // Check if item already in cart
                const existingItem = cart.find(item => item.id === id);

                if (existingItem) {
                    existingItem.qty++;
                } else {
                    cart.push({
                        id,
                        name,
                        price,
                        qty: 1
                    });
                }

                updateCart();
                showToast(`${name} added to cart!`);
            });
        });

        // Show cart modal
        cartIcon.addEventListener('click', () => {
            cartModal.classList.add('show');
            updateCart();
        });

        // Close cart modal
        cartModalClose.addEventListener('click', () => {
            cartModal.classList.remove('show');
        });

        // Show toast message
        function showToast(message) {
            document.getElementById('toastMessage').textContent = message;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Update cart display
        function updateCart() {
            cartCount.textContent = cart.reduce((total, item) => total + item.qty, 0);

            // Update cart items table
            cartItems.innerHTML = '';
            let totalItems = 0;
            let totalPrice = 0;

            cart.forEach(item => {
                const itemTotal = item.price * item.qty;
                totalItems += item.qty;
                totalPrice += itemTotal;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>${item.qty}</td>
                    <td>$${itemTotal.toFixed(2)}</td>
                `;
                cartItems.appendChild(row);
            });

            cartSummary.textContent = `Total: $${totalPrice.toFixed(2)}`;
            cartInfoSummary.textContent = `You have ${totalItems} ${totalItems === 1 ? 'item' : 'items'} in your cart`;

            // Update cart data for form submission
            document.getElementById('cartDataInput').value = JSON.stringify(cart);
        }

        // Form handling
        document.getElementById('cartForm').addEventListener('submit', function(e) {
            // Update hidden fields with visible values
            document.getElementById('checkoutName').value = document.getElementById('checkoutNameVisible').value;
            document.getElementById('checkoutEmail').value = document.getElementById('checkoutEmailVisible').value;
            document.getElementById('checkoutAddress').value = document.getElementById('checkoutAddressVisible').value;
        });
    </script>

    <?php
    // Only show cart modal automatically if there is an error or just placed an order
    $showCartModal = (!empty($error_message) || (isset($_GET['order']) && $_GET['order'] === 'success'));
    if ($showCartModal):
    ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("cartModal").classList.add("show");
            });
        </script>
    <?php endif; ?>
</body>

</html>