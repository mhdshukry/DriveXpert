<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveXpert</title>
    <link rel="stylesheet" href="./Assets/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sofia&family=Tangerine:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="./Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
    </div>
    <nav class="nav-links">
        <a href="#">Home</a>
        <a href="#">Rent</a>
        <a href="#">Cars</a>
        <a href="./aboutus.php">About Us</a>
        <a href="#">Contact Us</a>
    </nav>
    <div class="auth-buttons">
        <button class="btn" onclick="openModal('loginModal')">Sign In</button>
        <button class="btn" onclick="openModal('signupModal')">Sign Up</button>
    </div>
</header>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="slider">
            <div class="slide active-slide">
                <p class="tagline">The best way of renting</p>
                <h1>Best cars to make<br>Your <span class="highlight">drive easy</span></h1>
                <button class="hero-btn">Best Offers</button>
            </div>
            <div class="slide">
                <p class="tagline">Experience Comfort</p>
                <h1>Premium cars for<br>Your <span class="highlight">smooth ride</span></h1>
                <button class="hero-btn">Explore Cars</button>
            </div>
        </div>
    </div>

    <!-- Car Image with Background Layer -->
    <div class="car-image-containers">
        <!-- Car Image -->
        <img src="./Assets/Images/car.png" alt="Car" class="car-image">
    </div>

   <!-- Yellow Curve -->
   <div class="yellow-curve">
        <!-- Car Brand Logos -->
        <div class="car-brand-logos">
            <img src="./Assets/Images/tesla-removebg-preview.png" alt="Brand 1">
            <img src="./Assets/Images/Lamborghini-removebg-preview.png" alt="Brand 2">
            <img src="./Assets/Images/Rolls_Royce-removebg-preview.png" alt="Brand 3">
            <img src="./Assets/Images/benz-removebg-preview.png" alt="Brand 4">
            <img src="./Assets/Images/audi-removebg-preview.png" alt="Brand 5">
            <img src="./Assets/Images/bmw-removebg-preview.png" alt="Brand 6">
            <img src="./Assets/Images/ferrari-removebg-preview.png" alt="Brand 7">
            <img src="./Assets/Images/toyota-removebg-preview.png" alt="Brand 8">
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <!-- Headings -->
    <div class="features-heading">
        <p class="subheading">Why should you book with us?</p>
        <h2 class="main-heading">We compare car rental prices, <span class="highlight">you save!</span></h2>
    </div>

    <!-- Feature items wrapper to align horizontally -->
    <div class="feature-items-wrapper">
        <div class="feature-item">
            <div class="feature-image">
                <img src="Assets/Images/home-1.jpg" alt="Feature 1">
                <div class="feature-logo">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <p class="feature-title">No Extra Hidden Fees</p>
        </div>

        <div class="feature-item">
            <div class="feature-image">
                <img src="Assets/Images/home-2.jpg" alt="Feature 2">
                <div class="feature-logo">
                    <i class="fas fa-globe"></i>
                </div>
            </div>
            <p class="feature-title">Multilingual Service</p>
        </div>

        <div class="feature-item">
            <div class="feature-image">
                <img src="Assets/Images/home2.jpg" alt="Feature 3">
                <div class="feature-logo">
                    <i class="fas fa-exchange-alt"></i>
                </div>
            </div>
            <p class="feature-title">Flexible Returns</p>
        </div>

        <div class="feature-item">
            <div class="feature-image">
                <img src="Assets/Images/home-4.jpg" alt="Feature 4">
                <div class="feature-logo">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <p class="feature-title">100% You Can Trust</p>
        </div>
    </div>
</section>

<section class="steps-section">
    <div class="steps-heading">
        <p class="subheading">How It Works?</p>
        <h2 class="main-heading">Make It Happen In <span class="highlight"> 4 Steps</span></h2>
    </div>

    <div class="steps-wrapper">
        <div class="step-item">
            <div class="step-icon">
                <i class="fas fa-car"></i> <!-- Car icon -->
            </div>
            <h3 class="step-title">Select Your Car</h3>
            <p class="step-description">Choose from our wide range of available vehicles that suit your needs and budget.</p>
        </div>

        <div class="step-item">
            <div class="step-icon">
                <i class="fas fa-file-alt"></i> <!-- Document icon -->
            </div>
            <h3 class="step-title">Booking & Confirm</h3>
            <p class="step-description">Fill in your details and confirm your booking in a few simple steps.</p>
        </div>

        <div class="step-item">
            <div class="step-icon">
                <i class="fas fa-credit-card"></i> <!-- Payment icon -->
            </div>
            <h3 class="step-title">Booking Payment</h3>
            <p class="step-description">Securely pay for your booking through our various payment options.</p>
        </div>

        <div class="step-item">
            <div class="step-icon">
                <i class="fas fa-car-side"></i> <!-- Car-side icon -->
            </div>
            <h3 class="step-title">Enjoy The Car</h3>
            <p class="step-description">Pick up your car and enjoy a hassle-free driving experience with us.</p>
        </div>
    </div>
</section>


<section class="car-slider-section">
    <div class="slider-heading">
        <p class="subheading">Find Your Dream Car</p>
        <h2 class="main-heading">Browse Our Exclusive <span class="highlight">Car Collection</span></h2>
    </div>

    <div class="slider-wrapper">
        <div class="car-info">
            <h2 class="car-brand">LEXUS</h2>
            <h1 class="car-model">LC SERIES</h1>
        </div>

        <div class="car-image-container">
            <img src="Assets/Images/car5.png" alt="Car Image" class="car-image1">
        </div>

        <div class="slider-controls">
            <button class="slider-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-next"><i class="fas fa-chevron-right"></i></button>
        </div>

        <div class="car-buttons">
            <button class="btn rent-btn">Rent Now</button>
            <button class="btn details-btn" onclick="toggleSpecs()">Details</button>
        </div>

        <!-- Car Specs (Initially Hidden) -->
        <div class="car-specs-container" id="carSpecs">
            <button class="close-btn" onclick="toggleSpecs()">X</button>
            <div class="car-specs">
                <div class="spec-item">
                    <img src="Assets/images/lexus.png" alt="Logo" class="spec-icon">
                    <span class="spec-text">Lexus</span>
                </div>
                <div class="spec-item">
                    <img src="Assets/images/seat.png" alt="Seat Capacity" class="spec-icon">
                    <span class="spec-text">4 Seats</span>
                </div>
                <div class="spec-item">
                    <img src="Assets/images/speed.png" alt="Max Speed" class="spec-icon">
                    <span class="spec-text">250 Km/h</span>
                </div>
                <div class="spec-item">
                    <img src="Assets/images/fuel.png" alt="Litter/Km" class="spec-icon">
                    <span class="spec-text">1L/10km</span>
                </div>
                <div class="spec-item">
                    <img src="Assets/images/price.png" alt="Price/Day" class="spec-icon">
                    <span class="spec-text">$100/day</span>
                </div>
            </div>
            <button class="btn rent-now-btn">Rent Now</button>
        </div>
    </div>
</section>



<section class="stats-section">
    <div class="stats-heading">
        <p class="subheading">Find your car in one of our</p>
        <h2 class="main-heading">Find the perfect rental car <br>We compare car rental prices, <span class="highlight">you save!</span></h2>
    </div>

    <div class="stats-wrapper">
        <div class="stat-item">
            <h3 class="stat-number" data-target="145" data-target-format="">0</h3>
            <p>Countries</p>
        </div>
        <div class="stat-item">
            <h3 class="stat-number" data-target="1000" data-target-format="K">0</h3>
            <p>Locations</p>
        </div>
        <div class="stat-item">
            <h3 class="stat-number" data-target="27" data-target-format="+">0</h3>
            <p>Partners</p>
        </div>
        <div class="stat-item">
            <h3 class="stat-number" data-target="38" data-target-format="+">0</h3>
            <p>Languages</p>
        </div>
    </div>
</section>

<section class="faq-section">
    <!-- Heading for FAQ Section -->
    <div class="faq-heading">
        <p class="subheading">Got Questions?</p>
        <h2 class="main-heading">Frequently Asked <span class="highlight">Questions</span></h2>
    </div>

    <!-- FAQ items -->
    <div class="faq-items-wrapper">
        <div class="faq-item">
            <div class="faq-question-wrapper">
                <h3 class="faq-question">How do I book a rental car?</h3>
                <button class="faq-toggle">+</button>
            </div>
            <p class="faq-answer">To book a car, simply navigate to our "Rent" section, select your preferred car, choose the dates, and follow the booking steps.</p>
        </div>

        <div class="faq-item">
            <div class="faq-question-wrapper">
                <h3 class="faq-question">What documents do I need to rent a car?</h3>
                <button class="faq-toggle">+</button>
            </div>
            <p class="faq-answer">You will need a valid driver's license, identification (like a passport), and a credit card to complete the rental process.</p>
        </div>

        <div class="faq-item">
            <div class="faq-question-wrapper">
                <h3 class="faq-question">Is insurance included with the rental?</h3>
                <button class="faq-toggle">+</button>
            </div>
            <p class="faq-answer">Yes, basic insurance is included with all rentals. However, you can upgrade your insurance package for additional coverage.</p>
        </div>

        <div class="faq-item">
            <div class="faq-question-wrapper">
                <h3 class="faq-question">Can I return the car to a different location?</h3>
                <button class="faq-toggle">+</button>
            </div>
            <p class="faq-answer">Yes, we offer one-way rentals. You can return the car to a different location for an additional fee. Check with our team for available options.</p>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer class="footer-section">
    <div class="footer-container">
        <!-- Logo and Contact Info -->
        <div class="footer-column">
            <div class="footer-logo">
                <img src="./Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
            </div>
            <p class="footer-description">DriveXpert is your trusted car rental service provider. We offer a wide range of vehicles at the best prices to make your driving experience smooth and comfortable.</p>
        </div>

        <!-- Quick Links -->
        <div class="footer-column">
            <h4>Quick Links</h4>
            <ul class="footer-links">
                <li><a href="#">Home</a></li>
                <li><a href="#">Rent</a></li>
                <li><a href="#">Cars</a></li>
                <li><a href="./aboutus.php">About Us</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">FAQs</a></li>
            </ul>
        </div>

        <!-- Social Media Links -->
        <div class="footer-column">
            <h4>Follow Us</h4>
            <ul class="footer-social">
                <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
            </ul>
            <p class="footer-contact">Contact: +1 234 567 8901</p>
            <p class="footer-email">Email: info@drivexpert.com</p>
        </div>

        <!-- Newsletter Signup -->
        <div class="footer-column">
            <h4>Newsletter</h4>
            <p>Subscribe to our newsletter for the latest offers and updates!</p>
            <form class="footer-newsletter-form">
                <input type="email" placeholder="Enter your email" required>
                <button type="submit" class="btn newsletter-btn">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2024 DriveXpert. All Rights Reserved. | Privacy Policy | Terms & Conditions</p>
    </div>
</footer>



    <script>
        // Array of car details: images, brands, models
        const cars = [
            {
                image: 'Assets/Images/car5.png', // Replace with your actual image path
                brand: 'LEXUS',
                model: 'LC SERIES'
            },
            {
                image: 'Assets/Images/car2.png', // Replace with your actual image path
                brand: 'BMW',
                model: 'M SERIES'
            },
            {
                image: 'Assets/Images/car1.png', // Replace with your actual image path
                brand: 'MERCEDES',
                model: 'S CLASS'
            },
            {
                image: 'Assets/Images/car4.png', // Replace with your actual image path
                brand: 'AUDI',
                model: 'A SERIES'
            }
        ];

        let currentIndex = 0;
const carImageElement = document.querySelector('.car-image1');
const carBrandElement = document.querySelector('.car-brand');
const carModelElement = document.querySelector('.car-model');

// Function to update car information based on the current index
function updateCarInfo(direction) {
    const car = cars[currentIndex];

    // Remove any previous animations
    carImageElement.classList.remove('slide-in-left', 'slide-in-right', 'slide-out-left', 'slide-out-right');
    carBrandElement.classList.remove('slide-in-vertical', 'slide-out-vertical');
    carModelElement.classList.remove('slide-in-vertical', 'slide-out-vertical');

    // Add exit animations based on direction
    if (direction === 'next') {
        carImageElement.classList.add('slide-out-left');
    } else if (direction === 'prev') {
        carImageElement.classList.add('slide-out-right');
    }
    carBrandElement.classList.add('slide-out-vertical');
    carModelElement.classList.add('slide-out-vertical');

    setTimeout(() => {
        // Update the car information
        carImageElement.src = car.image;
        carBrandElement.textContent = car.brand;
        carModelElement.textContent = car.model;

        // Remove exit animations
        carImageElement.classList.remove('slide-out-left', 'slide-out-right');
        carBrandElement.classList.remove('slide-out-vertical');
        carModelElement.classList.remove('slide-out-vertical');

        // Add entry animations based on direction
        if (direction === 'next') {
            carImageElement.classList.add('slide-in-right');
        } else if (direction === 'prev') {
            carImageElement.classList.add('slide-in-left');
        }
        carBrandElement.classList.add('slide-in-vertical');
        carModelElement.classList.add('slide-in-vertical');
    }, 800); // Wait for the exit animation to complete
}

// Event listeners for next and previous buttons
document.querySelector('.slider-next').addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % cars.length; // Go to the next car, loop if at the end
    updateCarInfo('next');
});

document.querySelector('.slider-prev').addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + cars.length) % cars.length; // Go to the previous car, loop if at the beginning
    updateCarInfo('prev');
});


</script>




<script>
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

</script>


<script>
// Auto count up numbers when the section is in view
const counters = document.querySelectorAll('.stat-number');
const speed = 75; // The higher the number, the slower the count

counters.forEach(counter => {
    const updateCount = () => {
        const target = +counter.getAttribute('data-target'); // Get the target number
        const count = +counter.innerText;
        const format = counter.getAttribute('data-target-format'); // Get the format (K or +)
        
        // Calculate the increment
        const increment = target / speed;

        // If the current count is less than the target, increment the count
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(updateCount, 20);
        } else {
            // Format the final number with "K" or "+"
            let finalCount = target;
            if (target >= 1000) {
                finalCount = (target / 1000).toFixed(1) + 'K'; // Format as 1K, 2K, etc.
            }

            // Add "+" if required
            if (format === '+') {
                finalCount += '+';
            }

            counter.innerText = finalCount; // Display the formatted final number
        }
    };

    // Trigger the count when the section is in view
    const triggerCounting = () => {
        const sectionPosition = document.querySelector('.stats-section').getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;
        
        if (sectionPosition < screenPosition) {
            updateCount();
            window.removeEventListener('scroll', triggerCounting); // Remove event listener once counting starts
        }
    };

    window.addEventListener('scroll', triggerCounting);
});

</script>

<script>
    document.querySelectorAll('.faq-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const faqItem = button.parentElement.parentElement;

        // Hide all other FAQ answers first
        document.querySelectorAll('.faq-item').forEach(item => {
            if (item !== faqItem) {
                item.classList.remove('active');
                item.querySelector('.faq-toggle').textContent = '+'; // Reset button to '+'
            }
        });

        // Toggle the clicked FAQ item
        faqItem.classList.toggle('active');

        // Change button text based on whether FAQ is active
        if (faqItem.classList.contains('active')) {
            button.textContent = '-'; // Show '-' if active
        } else {
            button.textContent = '+'; // Revert back to '+' if not active
        }
    });
});

</script>

<script>
function toggleSpecs() {
    const specs = document.getElementById("carSpecs");

    if (specs.classList.contains('visible')) {
        specs.classList.remove('visible');
        setTimeout(() => {
            specs.style.display = 'none'; // Ensures display none after fade out
        }, 500); // Matches the transition duration
    } else {
        specs.style.display = 'block'; // Immediately set display block for smooth fade in
        setTimeout(() => specs.classList.add('visible'), 10); // Slight delay for transition
    }
}



</script>
</body>
</html>
