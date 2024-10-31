<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent a Car - DriveXpert</title>
    <link rel="stylesheet" href="../Assets/CSS/rent.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- Header Section -->
    <header class="header">
        <div class="logo">
            <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
        </div>
        <nav class="nav-links">
            <a href="./Home.php">Home</a>
            <a href="./rent.php">Rent</a>
            <a href="./Cars.php">Cars</a>
            <a href="./aboutus.php">About Us</a>
            <a href="./ContactUs.php">Contact Us</a>
        </nav>
        <div class="auth-buttons">
            <button class="btn logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </header>

    <!-- Rent Page Hero Section -->
    <section class="rent-hero">
        <div class="rent-hero-content">
            <p class="subheading">Choose from our premium fleet for a hassle-free car rental experience.</p>
            <h2 class="main-heading">Book Your <span class="highlight">Drive</span></h2>
        </div>
    </section>

    <!-- Rent Form Section -->
    <section class="rent-form-section">
        <h2>Rental <span class="highlight">Details</span></h2>
        <form class="rent-form">
            <!-- Car Selection -->
            <div class="form-group">
                <label for="car-brand">Select Car Brand & Model</label>
                <select id="car-brand" name="car-brand" required>
                    <option value="">Choose a Brand & Model</option>
                    <option value="Tesla Model 3">Tesla Model 3</option>
                    <option value="BMW X5">BMW X5</option>
                    <option value="Audi A6">Audi A6</option>
                    <option value="Mercedes-Benz E-Class">Mercedes-Benz E-Class</option>
                </select>
            </div>

            <!-- Personal Information -->
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="nic">NIC Number</label>
                <input type="text" id="nic" name="nic" required>
            </div>

            <!-- Reservation Date -->
            <div class="form-group date-group">
                <label>Reservation Date</label>
                <input type="date" id="date-from" name="date-from" required>
                <span>to</span>
                <input type="date" id="date-to" name="date-to" required>
            </div>

            <!-- Insurance Option -->
            <div class="form-group">
                <label for="insurance">Choose Insurance Plan</label>
                <select id="insurance" name="insurance" required>
                    <option value="Standard Coverage">Standard Coverage</option>
                    <option value="Full Coverage">Full Coverage</option>
                    <option value="Third-Party Only">Third-Party Only</option>
                </select>
            </div>

            <!-- Additional Options -->
            <h3>Additional Options</h3>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="gps" value="gps"> GPS Navigation System
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="child-seat" value="child-seat"> Child Safety Seat
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="additional-driver" value="additional-driver"> Additional Driver
                </label>
            </div>

            <!-- Payment Summary -->
            <div class="summary-section">
                <h3>Summary</h3>
                <p><strong>Car Model:</strong> <span id="selected-car"></span></p>
                <p><strong>Rental Duration:</strong> <span id="rental-duration"></span> days</p>
                <p><strong>Base Cost:</strong> <span id="base-cost">$200</span></p>
                <p><strong>Additional Fees:</strong> <span id="additional-fees">$0</span></p>
                <p><strong>Total Cost:</strong> <span id="total-cost">$200</span></p>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-group terms">
                <label>
                    <input type="checkbox" name="terms" required> I agree to the <a href="#">Terms & Conditions</a> of DriveXpert rentals.
                </label>
            </div>

            <!-- Submit Button -->
            <a href="./checkout.php" style="text-decoration:none"><button type="submit" class="btn submit-btn">Reserve Now</button></a>
        </form>
    </section>

    <!-- Footer Section -->
    <footer class="footer-section">
        <div class="footer-container">
            <!-- Logo and Contact Info -->
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
                </div>
                <p class="footer-description">DriveXpert is your trusted car rental service provider. We offer a wide range of vehicles at the best prices to make your driving experience smooth and comfortable.</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="./Home.php">Home</a></li>
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

</body>

</html>
