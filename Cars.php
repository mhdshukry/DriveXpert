<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveXpert</title>
    <link rel="stylesheet" href="./Assets/CSS/cars.css">
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
        <a href="./index.php">Home</a>
        <a href="./rent.php">Rent</a>
        <a href="./Cars.php">Cars</a>
        <a href="./aboutus.php">About Us</a>
        <a href="./ContactUs.php">Contact Us</a>
    </nav>
    <div class="auth-buttons">
        <button class="btn" onclick="openModal('loginModal')">Sign In</button>
        <button class="btn" onclick="openModal('signupModal')">Sign Up</button>
    </div>
</header>

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
            <a href="./rent.php" style="text-decoration:none"><button class="btn rent-now-btn">Rent Now</button></a>
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
                <li><a href="./index.php">Home</a></li>
                <li><a href="./rent.php">Rent</a></li>
                <li><a href="./Cars.php">Cars</a></li>
                <li><a href="./aboutus.php">About Us</a></li>
                <li><a href="./ContactUs.php">Contact Us</a></li>
                <li><a href="#faq">FAQs</a></li>
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

<script src="./Assets/JS/details.js"></script>
<script src="./Assets/JS/Cars.js"></script>
</body>
</html>