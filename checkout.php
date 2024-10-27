<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - DriveXpert</title>
    <link rel="stylesheet" href="./Assets/CSS/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
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
            <a href="#">Cars</a>
            <a href="./aboutus.php">About Us</a>
            <a href="./contactus.php">Contact Us</a>
        </nav>
        <div class="auth-buttons">
            <button class="btn" onclick="openModal('loginModal')">Sign In</button>
            <button class="btn" onclick="openModal('signupModal')">Sign Up</button>
        </div>
    </header>

    <!-- Checkout Hero Section -->
    <section class="checkout-hero">
        <div class="checkout-hero-content">
            <h2 class="main-heading">Checkout</h2>
            <p class="subheading">Review your booking and complete the payment</p>
        </div>
    </section>

    <!-- Checkout Details Section -->
    <section class="checkout-section">
        <h2>Booking <span class="highlight">Summary</span></h2>
        <div class="booking-summary">
            <p><strong>Car Model:</strong> Tesla Model 3</p>
            <p><strong>Rental Duration:</strong> 5 days</p>
            <p><strong>Pick-up Date:</strong> October 24, 2023</p>
            <p><strong>Drop-off Date:</strong> October 29, 2023</p>
            <p><strong>Additional Options:</strong> GPS, Child Safety Seat</p>
            <p><strong>Insurance:</strong> Full Coverage</p>
            <p><strong>Total Cost:</strong> $500</p>
        </div>
    </section>

    <!-- Payment Section -->
    <section class="payment-section">
        <h2>Payment <span class="highlight">Details</span></h2>
        <form class="payment-form" onsubmit="showSuccessPopup(event)">
            <!-- Payment Method -->
            <div class="form-group">
                <label for="payment-method">Choose Payment Method</label>
                <select id="payment-method" name="payment-method" required>
                    <option value="credit-card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="debit-card">Debit Card</option>
                </select>
            </div>

            <!-- Card Details (only for credit/debit card) -->
            <div class="card-details">
                <div class="form-group">
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number" name="card-number" required>
                </div>
                <div class="form-group">
                    <label for="card-holder">Card Holder Name</label>
                    <input type="text" id="card-holder" name="card-holder" required>
                </div>
                <div class="form-group">
                    <label for="expiry-date">Expiry Date</label>
                    <input type="month" id="expiry-date" name="expiry-date" required>
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" required>
                </div>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="form-group terms">
                <label>
                    <input type="checkbox" name="confirm" required> I confirm that the booking details are correct.
                </label>
            </div>

            <!-- Confirm Button -->
            <button type="submit" class="btn confirm-btn">Complete Reservation</button>
        </form>
    </section>

    <!-- Success Popup Modal -->
    <div id="success-popup" class="success-popup">
        <div class="popup-content">
            <i class="fas fa-check-circle success-icon"></i>
            <h3>Booking Successful!</h3>
            <p>Your reservation has been confirmed. We look forward to serving you!</p>
            <button onclick="closeSuccessPopup()" class="btn close-btn">Close</button>
        </div>
    </div>

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

    <script>
    // Function to display the success popup
    function showSuccessPopup(event) {
        event.preventDefault(); // Prevent form submission
        const popup = document.getElementById("success-popup");
        popup.style.display = "flex";

        // Auto-hide the popup after 7 seconds
        setTimeout(() => {
            popup.style.display = "none";
        }, 7000); // 7 seconds
    }

    // Function to close the success popup manually
    function closeSuccessPopup() {
        document.getElementById("success-popup").style.display = "none";
    }
</script>

</body>

</html>
