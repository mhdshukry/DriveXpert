let currentSlide = 0;
const slides = document.querySelectorAll('.slide');

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.classList.remove('active-slide');
        slide.style.opacity = 0;  // Start hiding the previous slide
        if (i === index) {
            slide.classList.add('active-slide');
            setTimeout(() => {
                slide.style.opacity = 1;  // Fade in the new slide
            }, 50);  // Small delay for smooth transition
        }
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

setInterval(nextSlide, 10000); // 10 seconds interval