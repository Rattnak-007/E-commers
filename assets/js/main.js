// Mobile menu toggle
document.getElementById("mobileMenu").addEventListener("click", function () {
  document.getElementById("navMenu").classList.toggle("show");
});

// Modern Slideshow functionality
let slideIndex = 0;
const slides = document.querySelectorAll(".slide");
const dots = document.querySelectorAll(".dot");
const prevBtn = document.getElementById("slidePrev");
const nextBtn = document.getElementById("slideNext");
let slideInterval;

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.toggle("active", i === index);
    dots[i].classList.toggle("active", i === index);
  });
  slideIndex = index;
}

function nextSlide() {
  let next = (slideIndex + 1) % slides.length;
  showSlide(next);
}

function prevSlide() {
  let prev = (slideIndex - 1 + slides.length) % slides.length;
  showSlide(prev);
}

function startSlideShow() {
  slideInterval = setInterval(nextSlide, 5000);
}

function stopSlideShow() {
  clearInterval(slideInterval);
}

// Arrow controls
nextBtn.addEventListener("click", () => {
  stopSlideShow();
  nextSlide();
  startSlideShow();
});
prevBtn.addEventListener("click", () => {
  stopSlideShow();
  prevSlide();
  startSlideShow();
});

// Dot controls
dots.forEach((dot, idx) => {
  dot.addEventListener("click", () => {
    stopSlideShow();
    showSlide(idx);
    startSlideShow();
  });
});

// Initialize slideshow
showSlide(slideIndex);
startSlideShow();

// Shopping cart functionality
let cartCount = 0;
const cartCountElement = document.getElementById("cartCount");
const cartIcon = document.getElementById("cartIcon");
const toast = document.getElementById("toast");
const toastMessage = document.getElementById("toastMessage");

// Cart modal elements
const cartModal = document.getElementById("cartModal");
const cartModalClose = document.getElementById("cartModalClose");
const cartItemsContainer = document.getElementById("cartItems");
const cartSummary = document.getElementById("cartSummary");

// Store cart items as {name, price, qty}
let cartItems = [];

// Add to cart buttons
document.querySelectorAll(".add-to-cart").forEach((button) => {
  button.addEventListener("click", function () {
    const productName = this.getAttribute("data-name");
    const productPrice = parseFloat(this.getAttribute("data-price"));
    cartCount++;
    cartCountElement.textContent = cartCount;

    // Check if item already in cart
    const idx = cartItems.findIndex((item) => item.name === productName);
    if (idx > -1) {
      cartItems[idx].qty += 1;
    } else {
      cartItems.push({ name: productName, price: productPrice, qty: 1 });
    }

    // Animate cart icon
    cartIcon.classList.add("pulse");
    setTimeout(() => cartIcon.classList.remove("pulse"), 500);

    // Show toast notification
    toastMessage.textContent = `${productName} added to cart!`;
    toast.classList.add("show");

    // Hide toast after 3 seconds
    setTimeout(() => toast.classList.remove("show"), 3000);
  });
});

// Render cart items and summary
function renderCart() {
  if (cartItems.length === 0) {
    cartItemsContainer.innerHTML = "<p>Your cart is empty.</p>";
    cartSummary.innerHTML = "";
    return;
  }
  let total = 0;
  cartItemsContainer.innerHTML = cartItems
    .map((item, idx) => {
      total += item.price * item.qty;
      return `
        <div class="cart-item-row">
          <span class="cart-item-name">${item.name}</span>
          <span>$${item.price.toFixed(2)}</span>
          <span class="cart-item-qty">
            <button type="button" data-idx="${idx}" class="qty-minus">-</button>
            <span>${item.qty}</span>
            <button type="button" data-idx="${idx}" class="qty-plus">+</button>
          </span>
          <button type="button" data-idx="${idx}" class="cart-item-remove">&times;</button>
        </div>
        `;
    })
    .join("");
  cartSummary.innerHTML = `
    <div class="cart-summary-row">
      <span>Subtotal</span>
      <span>$${total.toFixed(2)}</span>
    </div>
    <div class="cart-summary-row">
      <span>Total</span>
      <span>$${total.toFixed(2)}</span>
    </div>
  `;
}

// Show cart modal when cart icon is clicked
cartIcon.addEventListener("click", function () {
  renderCart();
  cartModal.classList.add("show");
});

// Cart item quantity and remove handlers
cartItemsContainer.addEventListener("click", function (e) {
  const idx = e.target.getAttribute("data-idx");
  if (e.target.classList.contains("qty-plus")) {
    cartItems[idx].qty += 1;
    cartCount++;
    cartCountElement.textContent = cartCount;
    renderCart();
  }
  if (e.target.classList.contains("qty-minus")) {
    if (cartItems[idx].qty > 1) {
      cartItems[idx].qty -= 1;
      cartCount--;
      cartCountElement.textContent = cartCount;
    } else {
      cartCount -= cartItems[idx].qty;
      cartItems.splice(idx, 1);
      cartCountElement.textContent = cartCount;
    }
    renderCart();
  }
  if (e.target.classList.contains("cart-item-remove")) {
    cartCount -= cartItems[idx].qty;
    cartItems.splice(idx, 1);
    cartCountElement.textContent = cartCount;
    renderCart();
  }
});

// Hide cart modal when close button is clicked
cartModalClose.addEventListener("click", function () {
  cartModal.classList.remove("show");
});

// Hide cart modal when clicking outside modal content
cartModal.addEventListener("click", function (e) {
  if (e.target === cartModal) {
    cartModal.classList.remove("show");
  }
});

// Handle checkout form submission
document.getElementById("cartForm").addEventListener("submit", function (e) {
  e.preventDefault();
  if (cartItems.length === 0) {
    alert("Your cart is empty.");
    return;
  }
  const name = document.getElementById("checkoutName").value.trim();
  const email = document.getElementById("checkoutEmail").value.trim();
  const address = document.getElementById("checkoutAddress").value.trim();
  if (!name || !email || !address) {
    alert("Please fill in all checkout fields.");
    return;
  }
  alert(
    "Thank you for your order, " +
      name +
      "!\nCheckout functionality coming soon."
  );
  cartModal.classList.remove("show");
  // Optionally clear cart
  cartItems = [];
  cartCount = 0;
  cartCountElement.textContent = cartCount;
});

// Smooth scroll to Featured Shoes when Shop is clicked
document.getElementById("shopLink").addEventListener("click", function (e) {
  e.preventDefault();
  document.getElementById("featured").scrollIntoView({ behavior: "smooth" });
});
