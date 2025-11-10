<?php
session_start(); // Public page; no auth required
$currentUri = $_SERVER['REQUEST_URI'] ?? 'ContactUs.php';
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
    <link rel="stylesheet" href="./Assets/CSS/contactus.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000&family=Oswald:wght@200..700&display=swap"
        rel="stylesheet">
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

    <!-- Contact Us Hero Section -->
    <section class="contact-hero">
        <div class="contact-hero-content">
            <p class="subheading">We're Here to Help</p>
            <h2 class="main-heading">Contact <span class="highlight">DriveXpert</span></h2>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="contact-info-section">
        <h2>Get in <span class="highlight">Touch</span></h2>
        <p class="section-description">For any inquiries, please feel free to contact us. We are available 24/7 to
            assist you.</p>
        <div class="contact-info-container">
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <p>123 DriveXpert Street, Colombo, Sri Lanka</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone-alt"></i>
                <p>+1 234 567 8901</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <p>info@drivexpert.com</p>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-form-section">
        <h2>Send Us a <span class="highlight">Message</span></h2>
        <form class="contact-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn submit-btn">Submit</button>
        </form>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <h2>Visit Our <span class="highlight">Office</span></h2>
        <p class="section-description">Feel free to stop by our office for in-person assistance.</p>
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


</body>

</html>