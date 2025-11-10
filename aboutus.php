<?php
session_start(); // Public page; no auth required
$currentUri = $_SERVER['REQUEST_URI'] ?? 'aboutus.php';
$signinUrl = 'auth.php?action=signin&return=' . urlencode($currentUri);
$signupUrl = 'auth.php?action=signup&return=' . urlencode($currentUri);
$logoutPath = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Admin/logout.php' : 'Client/logout.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveXpert</title>
    <link rel="icon" type="image/png" href="./Assets/Images/DriveXpert.png">
    <link rel="stylesheet" href="./Assets/CSS/aboutus.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Oswald:wght@200..700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sofia&family=Tangerine:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

    <!-- Header Section -->
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn logout-btn" onclick="window.location.href='./<?php echo $logoutPath; ?>'">Logout</button>
            <?php else: ?>
                <button class="btn" onclick="window.location.href='./<?php echo $signinUrl; ?>'">Sign In</button>
                <button class="btn" onclick="window.location.href='./<?php echo $signupUrl; ?>'">Sign Up</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- About Us Hero Section -->
    <section class="about-hero">
        <div class="about-hero-content">
            <p class="subheading">Providing premium car rental services around the world.</p>
            <h2 class="main-heading">About <span class="highlight">DriveXpert</span></h2>
        </div>
    </section>

    <!-- Vision Section with Car Image -->
    <section class="vision-section">
        <div class="vision-content">
            <h2>Our <span class="highlight">Vision</span></h2>
            <p>To be the leading car rental provider globally by offering unmatched convenience and premium services.
            </p>
        </div>
        <div class="vision-car-image">
            <img src="./Assets/Images/car-home.png" alt="Car Vision" class="uniform-car-img">
        </div>
    </section>

    <!-- Mission Section with Car Image -->
    <section class="mission-section">
        <div class="mission-car-image">
            <img src="./Assets/Images/car-s1-home.png" alt="Car Mission" class="uniform-car-img">
        </div>
        <div class="mission-content">
            <h2>Our <span class="highlight">Mission</span></h2>
            <p>Our mission is to make car rental accessible, affordable, and hassle-free while ensuring the highest
                level of customer satisfaction.</p>
        </div>
    </section>

    <!-- Achievements Section with Images -->
    <section class="achievements-section">
        <h2>Our <span class="highlight">Achievements</span></h2>
        <p class="section-description">We take pride in our accomplishments and commitment to delivering excellent
            service worldwide.</p>
        <div class="achievements-container">
            <div class="achievement-item">
                <img src="./Assets/Images/win.png" alt="1 Million Customers" class="uniform-car-icon">
                <p>Over 1 million satisfied customers</p>
            </div>
            <div class="achievement-item">
                <img src="./Assets/Images/win1.png" alt="Global Presence" class="uniform-car-icon">
                <p>Available in 145 countries worldwide</p>
            </div>
            <div class="achievement-item">
                <img src="./Assets/Images/win2.png" alt="Car Brand Partnerships" class="uniform-car-icon">
                <p>Partnerships with over 500 leading car brands</p>
            </div>
            <div class="achievement-item">
                <img src="./Assets/Images/win4.png" alt="Award-Winning Service" class="uniform-car-icon">
                <p>Winner of Best Rental Service 2023</p>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <h2>Our Global <span class="highlight">Presence</span></h2>
        <p class="section-description">Find our locations around the world, bringing convenient and reliable car rentals
            closer to you.</p>
        <div id="map">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.83543450932!2d144.9537353157678!3d-37.81627974238417!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad6433d3e616d81%3A0xf57768e28c1b92e2!2sVictoria%2C%20Australia!5e0!3m2!1sen!2sus!4v1616368499211!5m2!1sen!2sus"
                width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </section>


    <!-- Footer Section -->
    <footer class="footer-section">
        <div class="footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="./Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
                </div>
                <p class="footer-description">DriveXpert is your trusted car rental service provider. We offer a wide
                    range of vehicles at the best prices to make your driving experience smooth and comfortable.</p>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="./index.php">Home</a></li>
                    <li><a href="./rent.php">Rent</a></li>
                    <li><a href="./Cars.php">Cars</a></li>
                    <li><a href="./aboutus.php">About Us</a></li>
                    <li><a href="./ContactUs.php">Contact Us</a></li>
                    <li><a href="#">FAQs</a></li>
                </ul>
            </div>
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

    <style>
        .uniform-car-img {
            width: 480px;
            height: 280px;
            object-fit: cover;
            border-radius: 12px
        }

        .uniform-car-icon {
            width: 56px;
            height: 56px;
            object-fit: contain
        }

        @media (max-width:768px) {
            .uniform-car-img {
                width: 100%;
                height: 220px
            }
        }
    </style>
</body>

</html>