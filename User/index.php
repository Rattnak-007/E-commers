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
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/Style.css" />
    <style>
        /* ...existing code... */
        .cart-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            align-items: center;
            justify-content: center;
        }

        .cart-modal.show {
            display: flex !important;
        }

        .cart-modal-content {
            background: #fff;
            border-radius: 10px;
            max-width: 500px;
            width: 100%;
            margin: auto;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            position: relative;
        }

        /* Best modern style for hidden form (can be applied to any visible one too) */
        form#cartForm {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease-in-out;
        }

        form#cartForm input[type="text"],
        form#cartForm input[type="email"],
        form#cartForm input[type="hidden"],
        form#cartForm textarea {
            width: 100%;
            padding: 12px 16px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: #fff;
            transition: border-color 0.2s ease;
        }

        form#cartForm input:focus,
        form#cartForm textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
        }

        form#cartForm button[type="submit"] {
            background: #007bff;
            color: #fff;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form#cartForm button[type="submit"]:hover {
            background: #0056b3;
        }

        /* ...existing code... */
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
    <section class="slideshow">
        <div class="slideshow-container" id="slideshowContainer">
            <div class="slide active">
                <img
                    src="https://images.unsplash.com/photo-1519864600265-abb23847ef2c?auto=format&fit=crop&w=1200&q=80"
                    alt="Retro High-Tops" />
                <div class="caption">
                    Retro High-Tops
                    <p>Classic style, modern comfort. Limited edition now available.</p>
                </div>
            </div>
            <div class="slide">
                <img
                    src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80"
                    alt="Designer Loafers" />
                <div class="caption">
                    Designer Loafers
                    <p>Handcrafted luxury for every occasion. Shop exclusive designs.</p>
                </div>
            </div>
            <div class="slide">
                <img
                    src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=1200&q=80"
                    alt="Street Sneakers" />
                <div class="caption">
                    Street Sneakers
                    <p>Urban look for everyday wear. Trending styles for you.</p>
                </div>
            </div>
            <div class="slide">
                <img
                    src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80"
                    alt="UltraBoost Runners" />
                <div class="caption">
                    UltraBoost Runners
                    <p>Experience maximum comfort with our latest running technology.</p>
                </div>
            </div>
            <a class="prev" id="slidePrev">&#10094;</a>
            <a class="next" id="slideNext">&#10095;</a>
            <div class="dots" id="slideDots">
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
                            <h3 class="product-title">Summer Breeze Sandals</h3>
                            <div class="product-price">
                                <span class="current-price">$79.99</span>
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
        <div class="container"></div>
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
                    <tbody id="cartItems">
                        <!-- JS will fill this -->
                    </tbody>
                </table>
            </div>
            <div id="cartSummary" style="margin-bottom: 20px"></div>

            <!-- Order Details Preview (hidden, but needed for JS) -->
            <div style="display:none;">
                <span id="orderDetailName"></span>
                <span id="orderDetailEmail"></span>
                <span id="orderDetailAddress"></span>
                <span id="orderDetailTotalItems"></span>
                <span id="orderDetailTotalPrice"></span>
            </div>

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
    <script src="../assets/js/main.js"></script>
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