<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
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
                <a href="../logout.php" class="nav-icon" title="Logout">
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

    <!-- Categories Section -->
    <section class="categories" id="">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="categories-grid">
                <div class="category-card">
                    <i class="fas fa-running"></i>
                    <h3>Athletic</h3>
                    <p>Performance footwear for all sports</p>
                    <p class="category-stats">200+ models available</p>
                </div>
                <div class="category-card">
                    <i class="fas fa-street-view"></i>
                    <h3>Casual</h3>
                    <p>Everyday comfort with style</p>
                    <p class="category-stats">150+ styles to choose from</p>
                </div>
                <div class="category-card">
                    <i class="fas fa-hiking"></i>
                    <h3>Outdoor</h3>
                    <p>Adventure-ready footwear</p>
                    <p class="category-stats">Weatherproof and durable</p>
                </div>
                <div class="category-card">
                    <i class="fas fa-briefcase"></i>
                    <h3>Formal</h3>
                    <p>Elegant styles for special occasions</p>
                    <p class="category-stats">Premium materials</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="featured" class="featured-products">
        <div class="container">
            <h2 class="section-title">Featured Shoes</h2>
            <div class="products-grid">
                <!-- Product 1 -->
                <div class="product-card">
                    <div class="product-badge">New</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1543508282-6319a3e2621f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                            alt="Running Shoes" />
                        <div class="product-actions">
                            <div class="action-btn"><i class="fas fa-heart"></i></div>
                            <div class="action-btn"><i class="fas fa-eye"></i></div>
                            <div
                                class="action-btn add-to-cart"
                                data-id="1"
                                data-name="UltraBoost Runners"
                                data-price="129.99">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">UltraBoost Runners</h3>
                        <div class="product-price">
                            <span class="current-price">$129.99</span>
                            <span class="original-price">$149.99</span>
                        </div>
                        <p>Lightweight running shoes with maximum cushioning</p>
                        <div class="product-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                (142)
                            </div>
                            <div>In stock</div>
                        </div>
                    </div>
                </div>

                <!-- Product 2 -->
                <div class="product-card">
                    <div class="product-badge">Sale</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                            alt="Casual Shoes" />
                        <div class="product-actions">
                            <div class="action-btn"><i class="fas fa-heart"></i></div>
                            <div class="action-btn"><i class="fas fa-eye"></i></div>
                            <div
                                class="action-btn add-to-cart"
                                data-id="2"
                                data-name="Urban Classic Sneakers"
                                data-price="89.99">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Urban Classic Sneakers</h3>
                        <div class="product-price">
                            <span class="current-price">$89.99</span>
                            <span class="original-price">$109.99</span>
                        </div>
                        <p>Classic design meets modern comfort</p>
                        <div class="product-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                (98)
                            </div>
                            <div>In stock</div>
                        </div>
                    </div>
                </div>

                <!-- Product 3 -->
                <div class="product-card">
                    <div class="product-badge">Best Seller</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1600269452121-4f2416e55c28?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                            alt="Hiking Boots" />
                        <div class="product-actions">
                            <div class="action-btn"><i class="fas fa-heart"></i></div>
                            <div class="action-btn"><i class="fas fa-eye"></i></div>
                            <div
                                class="action-btn add-to-cart"
                                data-id="3"
                                data-name="TrailMaster Hiking Boots"
                                data-price="149.99">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">TrailMaster Hiking Boots</h3>
                        <div class="product-price">
                            <span class="current-price">$149.99</span>
                        </div>
                        <p>Waterproof and durable for all terrains</p>
                        <div class="product-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                (76)
                            </div>
                            <div>In stock</div>
                        </div>
                    </div>
                </div>

                <!-- Product 4 -->
                <div class="product-card">
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1560343090-f0409e92791a?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                            alt="Formal Shoes" />
                        <div class="product-actions">
                            <div class="action-btn"><i class="fas fa-heart"></i></div>
                            <div class="action-btn"><i class="fas fa-eye"></i></div>
                            <div
                                class="action-btn add-to-cart"
                                data-id="4"
                                data-name="Executive Oxfords"
                                data-price="119.99">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Executive Oxfords</h3>
                        <div class="product-price">
                            <span class="current-price">$119.99</span>
                        </div>
                        <p>Premium leather for business occasions</p>
                        <div class="product-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <i class="far fa-star"></i>
                                (53)
                            </div>
                            <div>In stock</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px">
                <a href="#" class="btn btn-secondary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Collections Section -->
    <section id="collections" class="collections-section">
        <div class="container">
            <h2 class="section-title">Collections</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-badge">Limited</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1519864600265-abb23847ef2c?auto=format&fit=crop&w=500&q=80"
                            alt="Retro High-Tops" />
                        <div class="product-actions">
                            <div class="action-btn"><i class="fas fa-heart"></i></div>
                            <div class="action-btn"><i class="fas fa-eye"></i></div>
                            <div
                                class="action-btn add-to-cart"
                                data-id="5"
                                data-name="Retro High-Tops"
                                data-price="139.99">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Retro High-Tops</h3>
                        <div class="product-price">
                            <span class="current-price">$139.99</span>
                        </div>
                        <p>Classic style with modern comfort</p>
                        <div class="product-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <i class="far fa-star"></i>
                                (34)
                            </div>
                            <div>Limited stock</div>
                        </div>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-badge">Exclusive</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=500&q=80"
                            alt="Designer Loafers" />
                        <div class="product-actions">
                            <div class="action-btn"><i class="fas fa-heart"></i></div>
                            <div class="action-btn"><i class="fas fa-eye"></i></div>
                            <div
                                class="action-btn add-to-cart"
                                data-id="6"
                                data-name="Designer Loafers"
                                data-price="159.99">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Designer Loafers</h3>
                        <div class="product-price">
                            <span class="current-price">$159.99</span>
                        </div>
                        <p>Handcrafted luxury for every occasion</p>
                        <div class="product-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                (21)
                            </div>
                            <div>Exclusive</div>
                        </div>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-badge">Trending</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=500&q=80"
                            alt="Street Sneakers" />
                        <div class="product-actions">
                            <div class="action-btn"><i class="fas fa-heart"></i></div>
                            <div class="action-btn"><i class="fas fa-eye"></i></div>
                            <div
                                class="action-btn add-to-cart"
                                data-id="7"
                                data-name="Street Sneakers"
                                data-price="99.99">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Street Sneakers</h3>
                        <div class="product-price">
                            <span class="current-price">$99.99</span>
                        </div>
                        <p>Urban look for everyday wear</p>
                        <div class="product-meta">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                (56)
                            </div>
                            <div>Trending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="new-arrivals" id="new-arrivals">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <div class="arrivals-grid">
                <div class="product-card">
                    <div class="product-badge">New</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=500&q=80"
                            alt="Summer Sandals" />
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Summer Breeze Sandals</h3>
                        <div class="product-price">
                            <span class="current-price">$79.99</span>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-badge">New</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1607522370275-f14206abe5d3?auto=format&fit=crop&w=500&q=80"
                            alt="Slip-On Sneakers" />
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Comfort Slip-Ons</h3>
                        <div class="product-price">
                            <span class="current-price">$94.99</span>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-badge">New</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=500&q=80"
                            alt="Basketball Shoes" />
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Pro Court Basketball</h3>
                        <div class="product-price">
                            <span class="current-price">$134.99</span>
                        </div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-badge">New</div>
                    <div class="product-image">
                        <img
                            src="https://images.unsplash.com/photo-1560343090-f0409e92791a?auto=format&fit=crop&w=500&q=80"
                            alt="Formal Loafers" />
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Leather Comfort Loafers</h3>
                        <div class="product-price">
                            <span class="current-price">$109.99</span>
                        </div>
                    </div>
                </div>
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
                        <a href="#"><i class="fas fa-clock"></i>Mon-Fri: 9AM - 8PM</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-clock"></i>Sat-Sun: 10AM - 6PM</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="copyright">
                <p>&copy; 2023 StepStyle. All rights reserved.</p>
            </div>
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
            <div id="cartItems"></div>
            <div id="cartSummary" style="margin-bottom: 20px"></div>
            <form id="cartForm">
                <div class="form-group">
                    <input
                        type="text"
                        id="checkoutName"
                        placeholder="Full Name"
                        required
                        style="margin-bottom: 10px; width: 100%; padding: 10px" />
                </div>
                <div class="form-group">
                    <input
                        type="email"
                        id="checkoutEmail"
                        placeholder="Email Address"
                        required
                        style="margin-bottom: 10px; width: 100%; padding: 10px" />
                </div>
                <div class="form-group">
                    <textarea
                        id="checkoutAddress"
                        placeholder="Shipping Address"
                        required
                        style="margin-bottom: 10px; width: 100%; padding: 10px"></textarea>
                </div>
                <button type="submit" class="btn btn-secondary" style="width: 100%">
                    Checkout
                </button>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>

</html>

</html>