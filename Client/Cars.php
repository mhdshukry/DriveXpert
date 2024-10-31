<?php
session_start();
include '../config.php'; // Ensure this file contains database connection details

// Query to fetch car data
$query = "SELECT brand, model, car_picture, seat_count, max_speed, km_per_liter, rent_per_day, logo_picture FROM cars WHERE availability = 1";
$result = $conn->query($query);

$cars = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveXpert</title>
    <link rel="stylesheet" href="../Assets/CSS/cars.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sofia&family=Tangerine:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
    </div>
    <nav class="nav-links">
        <a href="./Home.php">Home</a>
        <a href="./Rent.php">Rent</a>
        <a href="./Cars.php">Cars</a>
        <a href="./aboutus.php">About Us</a>
        <a href="./ContactUs.php">Contact Us</a>
    </nav>
    <div class="auth-buttons">
        <button class="btn logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</header>

<section class="car-slider-section">
    <div class="slider-heading">
        <p class="subheading">Find Your Dream Car</p>
        <h2 class="main-heading">Browse Our Exclusive <span class="highlight">Car Collection</span></h2>
    </div>

    <div class="slider-wrapper">
        <div class="car-info">
            <h2 class="car-brand">Brand</h2>
            <h1 class="car-model">Model</h1>
        </div>

        <div class="car-image-container">
            <img src="" alt="Car Image" class="car-image1">
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
    <!-- Car Brand (Logo not included) -->
    <div class="spec-item">
        <img src="" alt="Car Brand Logo" id="carLogo" class="spec-icon">
        <span class="spec-text" id="brandName"></span>
    </div>
    
    <!-- Seat Capacity -->
    <div class="spec-item">
        <img src="../Assets/images/seat.png" alt="Seat Capacity Icon" class="spec-icon">
        <span class="spec-text"><span id="seatCount"></span></span>
    </div>
    
    <!-- Max Speed -->
    <div class="spec-item">
        <img src="../Assets/images/speed.png" alt="Max Speed Icon" class="spec-icon">
        <span class="spec-text"><span id="maxSpeed"></span> Km/h</span>
    </div>
    
    <!-- Fuel Efficiency -->
    <div class="spec-item">
        <img src="../Assets/images/fuel.png" alt="Fuel Efficiency Icon" class="spec-icon">
        <span class="spec-text"><span id="fuelEfficiency"></span> Km/L</span>
    </div>
    
    <!-- Price per Day -->
    <div class="spec-item">
        <img src="../Assets/images/price.png" alt="Price per Day Icon" class="spec-icon">
        <span class="spec-text">$<span id="pricePerDay"></span></span>
    </div>
</div>

            <a href="./rent.php" style="text-decoration:none"><button class="btn rent-now-btn">Rent Now</button></a>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer class="footer-section">
        <div class="footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
                </div>
                <p class="footer-description">DriveXpert is your trusted car rental service provider. We offer a wide range of vehicles at the best prices to make your driving experience smooth and comfortable.</p>
            </div>
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

<script src="../Assets/JS/details.js"></script>
<script>
// Car data from PHP array
const cars = <?php echo json_encode($cars); ?>;
let currentIndex = 0;

// DOM elements for updating car details
const carImageElement = document.querySelector('.car-image1');
const carBrandElement = document.querySelector('.car-brand');
const carModelElement = document.querySelector('.car-model');
const seatCountElement = document.getElementById('seatCount');
const maxSpeedElement = document.getElementById('maxSpeed');
const fuelEfficiencyElement = document.getElementById('fuelEfficiency');
const pricePerDayElement = document.getElementById('pricePerDay');
const carLogoElement = document.getElementById('carLogo');
const brandNameElement = document.getElementById('brandName');

// Function to update car information with animations
function updateCarInfo(direction) {
    const car = cars[currentIndex];

    // Remove previous animations
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
        carImageElement.src = "../" + car.car_picture;
        carBrandElement.textContent = car.brand;
        carModelElement.textContent = car.model;
        seatCountElement.textContent = car.seat_count;
        maxSpeedElement.textContent = car.max_speed;
        fuelEfficiencyElement.textContent = car.km_per_liter;
        pricePerDayElement.textContent = car.rent_per_day;
        carLogoElement.src = "../" + car.logo_picture;
        brandNameElement.textContent = car.brand;

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

// Initialize with the first car's details
updateCarInfo();

// Event listeners for next and previous buttons
document.querySelector('.slider-next').addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % cars.length;
    updateCarInfo('next');
});

document.querySelector('.slider-prev').addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + cars.length) % cars.length;
    updateCarInfo('prev');
});
</script>
</body>
</html>
