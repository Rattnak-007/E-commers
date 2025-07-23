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

// Shopping cart
let cartCount = 0;
const cartCountElement = document.getElementById("cartCount");
const cartIcon = document.getElementById("cartIcon");
const toast = document.getElementById("toast");
const toastMessage = document.getElementById("toastMessage");

// Cart modal
const cartModal = document.getElementById("cartModal");
const cartModalClose = document.getElementById("cartModalClose");
const cartItemsContainer = document.querySelector(
  "#cartItemsTableWrapper tbody#cartItems"
);
const cartSummary = document.getElementById("cartSummary");
const cartInfoSummary = document.getElementById("cartInfoSummary");

// Order details elements
const orderDetailName = document.getElementById("orderDetailName");
const orderDetailEmail = document.getElementById("orderDetailEmail");
const orderDetailAddress = document.getElementById("orderDetailAddress");
const orderDetailTotalItems = document.getElementById("orderDetailTotalItems");
const orderDetailTotalPrice = document.getElementById("orderDetailTotalPrice");

let cartItems = [];

// Add to cart
document.querySelectorAll(".add-to-cart").forEach((button) => {
  button.addEventListener("click", function () {
    const productName = this.getAttribute("data-name");
    const productPrice = parseFloat(this.getAttribute("data-price"));
    cartCount++;
    cartCountElement.textContent = cartCount;

    const idx = cartItems.findIndex((item) => item.name === productName);
    if (idx > -1) {
      cartItems[idx].qty += 1;
    } else {
      cartItems.push({ name: productName, price: productPrice, qty: 1 });
    }

    cartIcon.classList.add("pulse");
    setTimeout(() => cartIcon.classList.remove("pulse"), 500);

    toastMessage.textContent = `${productName} added to cart!`;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3000);
  });
});

function renderOrderDetails() {
  const name = document.getElementById("checkoutName").value.trim();
  const email = document.getElementById("checkoutEmail").value.trim();
  const address = document.getElementById("checkoutAddress").value.trim();
  let totalQty = 0;
  let total = 0;

  cartItems.forEach((item) => {
    totalQty += item.qty;
    total += item.price * item.qty;
  });

  orderDetailName.textContent = name || "-";
  orderDetailEmail.textContent = email || "-";
  orderDetailAddress.textContent = address || "-";
  orderDetailTotalItems.textContent = totalQty;
  orderDetailTotalPrice.textContent = `$${total.toFixed(2)}`;
}

function renderCart() {
  renderOrderDetails();

  if (cartItems.length === 0) {
    cartInfoSummary.innerHTML = `<span>Your cart is empty. Add products to see them here.</span>`;
    cartItemsContainer.innerHTML = `<tr><td colspan="5" style="text-align:center;">Your cart is empty.</td></tr>`;
    cartSummary.innerHTML = "";
    return;
  }

  let total = 0;
  let totalQty = 0;
  cartItems.forEach((item) => {
    total += item.price * item.qty;
    totalQty += item.qty;
  });

  cartInfoSummary.innerHTML = `
    <span>
      <strong>${totalQty}</strong> item${
    totalQty > 1 ? "s" : ""
  } in cart &mdash; 
      <strong>Total: $${total.toFixed(2)}</strong>
    </span>
  `;

  cartItemsContainer.innerHTML = cartItems
    .map((item, idx) => {
      const itemTotal = item.price * item.qty;
      return `
      <tr>
        <td>${item.name}</td>
        <td>$${item.price.toFixed(2)}</td>
        <td>
          <button type="button" data-idx="${idx}" class="qty-minus">-</button>
          <span style="margin:0 8px;">${item.qty}</span>
          <button type="button" data-idx="${idx}" class="qty-plus">+</button>
        </td>
        <td>$${itemTotal.toFixed(2)}</td>
        <td>
          <button type="button" data-idx="${idx}" class="cart-item-remove" style="color:red;border:none;background:none;font-size:18px;">&times;</button>
        </td>
      </tr>
    `;
    })
    .join("");

  cartSummary.innerHTML = `
    <div class="cart-summary-row"><span>Subtotal</span><span>$${total.toFixed(
      2
    )}</span></div>
    <div class="cart-summary-row"><span>Total</span><span>$${total.toFixed(
      2
    )}</span></div>
  `;
}

cartIcon.addEventListener("click", function () {
  renderCart();

  let totalQty = 0;
  let total = 0;
  cartItems.forEach((item) => {
    totalQty += item.qty;
    total += item.price * item.qty;
  });

  toastMessage.textContent =
    cartItems.length > 0
      ? `You have ${totalQty} item${
          totalQty > 1 ? "s" : ""
        } in your cart. Total: $${total.toFixed(2)}`
      : "Your cart is empty.";

  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 3000);

  cartModal.classList.add("show");
});

// Fix: open cart modal when clicking the cart icon or its children
["click", "touchstart"].forEach((evtType) => {
  cartIcon.addEventListener(evtType, function (e) {
    // Prevent default and stop propagation
    e.preventDefault();
    e.stopPropagation();
    renderCart();

    let totalQty = 0;
    let total = 0;
    cartItems.forEach((item) => {
      totalQty += item.qty;
      total += item.price * item.qty;
    });

    toastMessage.textContent =
      cartItems.length > 0
        ? `You have ${totalQty} item${
            totalQty > 1 ? "s" : ""
          } in your cart. Total: $${total.toFixed(2)}`
        : "Your cart is empty.";

    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3000);

    cartModal.classList.add("show");
  });
});

// Quantity + Remove
cartItemsContainer.addEventListener("click", function (e) {
  const idx = e.target.getAttribute("data-idx");
  if (!idx) return;

  if (e.target.classList.contains("qty-plus")) {
    cartItems[idx].qty += 1;
    cartCount++;
  } else if (e.target.classList.contains("qty-minus")) {
    if (cartItems[idx].qty > 1) {
      cartItems[idx].qty -= 1;
      cartCount--;
    } else {
      cartCount -= cartItems[idx].qty;
      cartItems.splice(idx, 1);
    }
  } else if (e.target.classList.contains("cart-item-remove")) {
    cartCount -= cartItems[idx].qty;
    cartItems.splice(idx, 1);
  }
  cartCountElement.textContent = cartCount;
  renderCart();
});

// Close modal
cartModalClose.addEventListener("click", () =>
  cartModal.classList.remove("show")
);
cartModal.addEventListener("click", (e) => {
  if (e.target === cartModal) cartModal.classList.remove("show");
});

// Checkout submit
document.getElementById("cartForm").addEventListener("submit", function (e) {
  const name = document.getElementById("checkoutNameVisible").value.trim();
  const email = document.getElementById("checkoutEmailVisible").value.trim();
  const address = document
    .getElementById("checkoutAddressVisible")
    .value.trim();

  if (cartItems.length === 0) {
    alert("Your cart is empty.");
    e.preventDefault();
    return;
  }
  // Validate email format
  if (!name || !email || !address) {
    alert("Please fill in all checkout fields.");
    e.preventDefault();
    return;
  }
  // Fill hidden fields for PHP
  document.getElementById("checkoutName").value = name;
  document.getElementById("checkoutEmail").value = email;
  document.getElementById("checkoutAddress").value = address;
  document.getElementById("cartDataInput").value = JSON.stringify(cartItems);
});

// Optionally, clear cartItems if order placed (after reload)
window.addEventListener("DOMContentLoaded", function () {
  if (document.querySelector(".cart-modal-content div[style*='color:green']")) {
    cartItems = [];
    cartCount = 0;
    cartCountElement.textContent = "0";
    renderCart();
  }
});

// Smooth scroll
document.getElementById("shopLink").addEventListener("click", function (e) {
  e.preventDefault();
  document.getElementById("featured").scrollIntoView({ behavior: "smooth" });
});

// Live order detail update
[
  "checkoutNameVisible",
  "checkoutEmailVisible",
  "checkoutAddressVisible",
].forEach((id) => {
  const input = document.getElementById(id);
  if (input) {
    input.addEventListener("input", function () {
      // Update order details preview using visible fields
      const nameVal = document
        .getElementById("checkoutNameVisible")
        .value.trim();
      const emailVal = document
        .getElementById("checkoutEmailVisible")
        .value.trim();
      const addressVal = document
        .getElementById("checkoutAddressVisible")
        .value.trim();
      let totalQty = 0;
      let total = 0;
      cartItems.forEach((item) => {
        totalQty += item.qty;
        total += item.price * item.qty;
      });
      orderDetailName.textContent = nameVal || "-";
      orderDetailEmail.textContent = emailVal || "-";
      orderDetailAddress.textContent = addressVal || "-";
      orderDetailTotalItems.textContent = totalQty;
      orderDetailTotalPrice.textContent = `$${total.toFixed(2)}`;
    });
  }
});
["checkoutName", "checkoutEmail", "checkoutAddress"].forEach((id) => {
  const input = document.getElementById(id);
  if (input) {
    input.addEventListener("input", renderOrderDetails);
  }
});
["checkoutName", "checkoutEmail", "checkoutAddress"].forEach((id) => {
  const input = document.getElementById(id);
  if (input) {
    input.addEventListener("input", renderOrderDetails);
  }
});
