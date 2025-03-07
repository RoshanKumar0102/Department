// Disable Right-Click on the Page
document.addEventListener("contextmenu", (event) => event.preventDefault()); // Prevent the default right-click menu

// Slideshow Functionality
let slideIndex = 0;

function showSlide(n) {
  const slides = document.querySelectorAll(".slide");
  slides.forEach((slide) => (slide.style.display = "none")); // Hide all slides

  slideIndex = (n + slides.length) % slides.length; // Loop back to the first slide if at the end
  slides[slideIndex].style.display = "block"; // Show the current slide

  const currentSlide = slides[slideIndex];
  const video = currentSlide.querySelector("video");

  if (video) {
    video.loop = false; // Ensure the video doesn't loop
    video.currentTime = 0; // Reset video to the start
    video.play().catch((error) => console.error("Video playback failed:", error)); // Play the video

    video.onended = () => {
      showSlide(slideIndex + 1); // Move to the next slide after the video ends
    };
  } else {
    setTimeout(() => {
      showSlide(slideIndex + 1); // Move to the next slide after 3 seconds
    }, 3000); // Adjust delay as needed for image slides
  }
}

// Start the slideshow
document.addEventListener("DOMContentLoaded", () => {
  showSlide(0); // Start with the first slide
});
  window.addEventListener('scroll', function () {
    const backgroundSection = document.querySelector('.background-section');
    if (window.scrollY > 50) {
      backgroundSection.classList.add('scrolled');
    } else {
      backgroundSection.classList.remove('scrolled');
    }
  });
document.addEventListener("DOMContentLoaded", () => {
  const slideshows = document.querySelectorAll(".slideshow-container");

  slideshows.forEach((slideshow) => {
    const images = slideshow.querySelectorAll(".slideshow-images img");
    let currentIndex = 0;
    const slideInterval = 2000; // Change slides every 3 seconds

    // Function to show the current slide
    function showSlide(index) {
      images.forEach((img, i) => {
        img.style.display = i === index ? "block" : "none"; // Show the current image
      });
    }

    // Cycle to the next slide
    function nextSlide() {
      currentIndex = (currentIndex + 1) % images.length; // Increment index and loop
      showSlide(currentIndex);
    }

    // Initialize slideshow
    showSlide(currentIndex);
    setInterval(nextSlide, slideInterval);
  });
});

// Toggle Table Rows Functionality
function toggleRows(buttonId, tableId) {
  const button = document.getElementById(buttonId);
  const hiddenRows = document.querySelectorAll(`#${tableId} .hidden-row`);
  let areRowsHidden = Array.from(hiddenRows).some(
    (row) => row.style.display === "none" || row.style.display === ""
  );

  hiddenRows.forEach((row) => {
    row.style.display = areRowsHidden ? "table-row" : "none";
  });

  button.textContent = areRowsHidden ? "Show Less" : "Show More";
}

// Attach event listeners to buttons
document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("toggle-btn-1")
    .addEventListener("click", () => toggleRows("toggle-btn-1", "publication-table-1"));

  document
    .getElementById("toggle-btn-2")
    .addEventListener("click", () => toggleRows("toggle-btn-2", "publication-table-2"));

  document
    .getElementById("toggle-btn-3")
    .addEventListener("click", () => toggleRows("toggle-btn-3", "publication-table-3"));

  document
    .getElementById("toggle-btn-4")
    .addEventListener("click", () => toggleRows("toggle-btn-4", "publication-table-4"));
});