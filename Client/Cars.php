<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php?action=signin');
    exit;
}
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
    <link rel="icon" type="image/png" href="../Assets/Images/DriveXpert.png">
    <link rel="stylesheet" href="../Assets/CSS/cars.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sofia&family=Tangerine:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>

<body data-asset-prefix="../">
    <header class="header">
        <div class="logo">
            <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
        </div>
        <nav class="nav-links">
            <a href="./Home.php">Home</a>
            <a href="./rent.php">Rent</a>
            <a href="./Cars.php" class="active">Cars</a>
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
                <button class="btn rent-btn" onclick="window.location.href='rent.php'">Rent Now</button>
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
                        <img src="../Assets/Images/seat.png" alt="Seat Capacity Icon" class="spec-icon">
                        <span class="spec-text"><span id="seatCount"></span></span>
                    </div>

                    <!-- Max Speed -->
                    <div class="spec-item">
                        <img src="../Assets/Images/speed.png" alt="Max Speed Icon" class="spec-icon">
                        <span class="spec-text"><span id="maxSpeed"></span> Km/h</span>
                    </div>

                    <!-- Fuel Efficiency -->
                    <div class="spec-item">
                        <img src="../Assets/Images/fuel.png" alt="Fuel Efficiency Icon" class="spec-icon">
                        <span class="spec-text"><span id="fuelEfficiency"></span> Km/L</span>
                    </div>

                    <!-- Price per Day -->
                    <div class="spec-item">
                        <img src="../Assets/Images/price.png" alt="Price per Day Icon" class="spec-icon">
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
                <p class="footer-description">DriveXpert is your trusted car rental service provider. We offer a wide
                    range of vehicles at the best prices to make your driving experience smooth and comfortable.</p>
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

    <?php include __DIR__ . '/status_fab.php'; ?>

    <script src="../Assets/JS/details.js"></script>
    <script>
        const carData = <?php echo json_encode($cars); ?>;
        const cars = Array.isArray(carData) ? carData : [];
        let currentIndex = 0;

        const carImageElement = document.querySelector('.car-image1');
        const carBrandElement = document.querySelector('.car-brand');
        const carModelElement = document.querySelector('.car-model');
        const seatCountElement = document.getElementById('seatCount');
        const maxSpeedElement = document.getElementById('maxSpeed');
        const fuelEfficiencyElement = document.getElementById('fuelEfficiency');
        const pricePerDayElement = document.getElementById('pricePerDay');
        const carLogoElement = document.getElementById('carLogo');
        const brandNameElement = document.getElementById('brandName');
        const nextButton = document.querySelector('.slider-next');
        const prevButton = document.querySelector('.slider-prev');
        const rentButton = document.querySelector('.rent-btn');
        const rentNowButton = document.querySelector('.rent-now-btn');
        const detailsButton = document.querySelector('.details-btn');
        const specsContainer = document.getElementById('carSpecs');

        function disableButton(button, shouldDisable) {
            if (!button) {
                return;
            }
            if (shouldDisable) {
                button.setAttribute('disabled', 'disabled');
                button.classList.add('disabled');
                const wrapperLink = button.closest('a');
                if (wrapperLink) {
                    wrapperLink.setAttribute('aria-disabled', 'true');
                }
            } else {
                button.removeAttribute('disabled');
                button.classList.remove('disabled');
                const wrapperLink = button.closest('a');
                if (wrapperLink) {
                    wrapperLink.removeAttribute('aria-disabled');
                }
            }
        }

        function setPlaceholderState() {
            if (carImageElement) {
                carImageElement.src = '../Assets/Images/placeholder.png';
                carImageElement.alt = 'Car image placeholder';
            }
            if (carBrandElement) {
                carBrandElement.textContent = 'No Cars';
            }
            if (carModelElement) {
                carModelElement.textContent = 'Available';
            }
            if (seatCountElement) {
                seatCountElement.textContent = '—';
            }
            if (maxSpeedElement) {
                maxSpeedElement.textContent = '—';
            }
            if (fuelEfficiencyElement) {
                fuelEfficiencyElement.textContent = '—';
            }
            if (pricePerDayElement) {
                pricePerDayElement.textContent = '—';
            }
            if (carLogoElement) {
                carLogoElement.src = '../Assets/Images/placeholder-logo.png';
                carLogoElement.alt = 'Brand logo placeholder';
            }
            if (brandNameElement) {
                brandNameElement.textContent = 'No Brand';
            }
        }

        function populateCarDetails(car) {
            const picturePath = car?.car_picture ? `../${car.car_picture}` : '../Assets/Images/placeholder.png';
            const logoPath = car?.logo_picture ? `../${car.logo_picture}` : '../Assets/Images/placeholder-logo.png';

            if (carImageElement) {
                carImageElement.src = picturePath;
                const readableBrand = car?.brand ?? 'Car';
                const readableModel = car?.model ?? '';
                const altText = `${readableBrand} ${readableModel}`.trim() || 'Car image';
                carImageElement.alt = altText;
            }
            if (carBrandElement) {
                carBrandElement.textContent = car?.brand || 'N/A';
            }
            if (carModelElement) {
                carModelElement.textContent = car?.model || 'N/A';
            }
            if (seatCountElement) {
                seatCountElement.textContent = car?.seat_count ? `${car.seat_count} Seats` : '—';
            }
            if (maxSpeedElement) {
                maxSpeedElement.textContent = car?.max_speed || '—';
            }
            if (fuelEfficiencyElement) {
                fuelEfficiencyElement.textContent = car?.km_per_liter || '—';
            }
            if (pricePerDayElement) {
                pricePerDayElement.textContent = car?.rent_per_day || '—';
            }
            if (carLogoElement) {
                carLogoElement.src = logoPath;
                carLogoElement.alt = `${car?.brand || 'Car'} logo`;
            }
            if (brandNameElement) {
                brandNameElement.textContent = car?.brand || 'No Brand';
            }
        }

        function updateCarInfo(direction) {
            const car = cars[currentIndex];
            if (!car) {
                setPlaceholderState();
                return;
            }

            if (!carImageElement || !carBrandElement || !carModelElement) {
                populateCarDetails(car);
                return;
            }

            carImageElement.classList.remove('slide-in-left', 'slide-in-right', 'slide-out-left', 'slide-out-right');
            carBrandElement.classList.remove('slide-in-vertical', 'slide-out-vertical');
            carModelElement.classList.remove('slide-in-vertical', 'slide-out-vertical');

            if (!direction) {
                populateCarDetails(car);
                return;
            }

            if (direction === 'next') {
                carImageElement.classList.add('slide-out-left');
            } else if (direction === 'prev') {
                carImageElement.classList.add('slide-out-right');
            }
            carBrandElement.classList.add('slide-out-vertical');
            carModelElement.classList.add('slide-out-vertical');

            setTimeout(() => {
                populateCarDetails(car);

                carImageElement.classList.remove('slide-out-left', 'slide-out-right');
                carBrandElement.classList.remove('slide-out-vertical');
                carModelElement.classList.remove('slide-out-vertical');

                if (direction === 'next') {
                    carImageElement.classList.add('slide-in-right');
                } else if (direction === 'prev') {
                    carImageElement.classList.add('slide-in-left');
                }
                carBrandElement.classList.add('slide-in-vertical');
                carModelElement.classList.add('slide-in-vertical');
            }, 800);
        }

        const hasCars = Array.isArray(cars) && cars.length > 0;
        const multipleCars = hasCars && cars.length > 1;

        if (hasCars) {
            updateCarInfo();
            disableButton(nextButton, !multipleCars);
            disableButton(prevButton, !multipleCars);
            disableButton(rentButton, false);
            disableButton(detailsButton, false);
            disableButton(rentNowButton, false);

            if (multipleCars) {
                nextButton?.addEventListener('click', () => {
                    currentIndex = (currentIndex + 1) % cars.length;
                    updateCarInfo('next');
                });

                prevButton?.addEventListener('click', () => {
                    currentIndex = (currentIndex - 1 + cars.length) % cars.length;
                    updateCarInfo('prev');
                });
            }
        } else {
            setPlaceholderState();
            disableButton(nextButton, true);
            disableButton(prevButton, true);
            disableButton(rentButton, true);
            disableButton(detailsButton, true);
            disableButton(rentNowButton, true);
            if (specsContainer) {
                specsContainer.classList.remove('visible');
                specsContainer.style.display = 'none';
            }
        }
    </script>
</body>

</html>