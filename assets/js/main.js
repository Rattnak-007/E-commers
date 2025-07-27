document.addEventListener("DOMContentLoaded", function () {
  // Slideshow functionality
  const slides = document.querySelectorAll(".slide");
  const dots = document.querySelectorAll(".dot");
  const prevBtn = document.getElementById("slidePrev");
  const nextBtn = document.getElementById("slideNext");
  let currentSlide = 0;
  let slideInterval;

  // Initialize slideshow
  function initSlideshow() {
    // Set initial active slide
    showSlide(currentSlide);

    // Start auto rotation
    startAutoRotation();

    // Event listeners
    prevBtn.addEventListener("click", () => {
      pauseAutoRotation();
      prevSlide();
      startAutoRotation();
    });

    nextBtn.addEventListener("click", () => {
      pauseAutoRotation();
      nextSlide();
      startAutoRotation();
    });

    // Add click events for dots
    dots.forEach((dot, index) => {
      dot.addEventListener("click", () => {
        pauseAutoRotation();
        showSlide(index);
        startAutoRotation();
      });
    });
  }

  // Show specific slide
  function showSlide(index) {
    // Reset all slides and dots
    slides.forEach((slide) => slide.classList.remove("active"));
    dots.forEach((dot) => dot.classList.remove("active"));

    // Set new active slide and dot
    slides[index].classList.add("active");
    dots[index].classList.add("active");

    // Reset and restart progress bar animation
    const progressBars = document.querySelectorAll(".progress-bar");
    progressBars.forEach((bar) => (bar.style.width = "0%"));

    // Update current slide index
    currentSlide = index;
  }

  // Next slide
  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
  }

  // Previous slide
  function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(currentSlide);
  }

  // Start auto rotation
  function startAutoRotation() {
    slideInterval = setInterval(nextSlide, 5000);
  }

  // Pause auto rotation
  function pauseAutoRotation() {
    clearInterval(slideInterval);
  }

  // Initialize the slideshow
  initSlideshow();
});
