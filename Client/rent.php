<?php
session_start();
include '../config.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to rent a car.'); window.location.href='../auth.php';</script>";
    exit;
}

// Fetch available cars from the database
$carQuery = "SELECT car_id, brand, model, rent_per_day FROM cars WHERE availability = 1";
$carResult = $conn->query($carQuery);

$cars = [];
if ($carResult->num_rows > 0) {
    while ($car = $carResult->fetch_assoc()) {
        $cars[] = $car;
    }
}

// Handle form submission for rental
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $car_id = $_POST['car_id'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    $gps = isset($_POST['gps']) ? 5 : 0;
    $child_seat = isset($_POST['child_seat']) ? 8 : 0;
    $additional_driver = isset($_POST['additional_driver']) ? 12 : 0;

    // Calculate rental duration in days
    $from_date = new DateTime($date_from);
    $to_date = new DateTime($date_to);
    $rental_duration = $from_date->diff($to_date)->days;

    // Fetch daily rent for the selected car
    $carQuery = $conn->prepare("SELECT rent_per_day FROM cars WHERE car_id = ?");
    $carQuery->bind_param("i", $car_id);
    $carQuery->execute();
    $carQuery->bind_result($rent_per_day);
    $carQuery->fetch();
    $carQuery->close();

    // Calculate total cost
    $base_cost = $rent_per_day * $rental_duration;
    $additional_fees = $gps + $child_seat + $additional_driver;
    $total_cost = $base_cost + $additional_fees;

    // Insert rental data into rentals table
    $stmt = $conn->prepare("INSERT INTO rentals (user_id, car_id, date_from, date_to, total_cost, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iissd", $user_id, $car_id, $date_from, $date_to, $total_cost);

    if ($stmt->execute()) {
        echo "<script>alert('Rental booked successfully!'); window.location.href='client_booking.php';</script>";
    } else {
        echo "<script>alert('Error booking rental. Please try again.');</script>";
    }
    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent a Car - DriveXpert</title>
    <link rel="stylesheet" href="../Assets/CSS/rent.css">
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

<!-- Rent Form Section -->
<section class="rent-form-section">
    <h2>Rental Details</h2>
    <form method="post" class="rent-form" oninput="updateSummary()">
        <!-- Car Selection -->
        <div class="form-group">
            <label for="car-id">Select Car</label>
            <select id="car-id" name="car_id" required onchange="updateCarDetails(this)">
                <option value="" data-price="0">Choose a Car</option>
                <?php foreach ($cars as $car): ?>
                    <option value="<?= $car['car_id'] ?>" data-price="<?= $car['rent_per_day'] ?>">
                        <?= $car['brand'] . " " . $car['model'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Reservation Dates -->
        <div class="form-group">
            <label>Reservation Dates</label>
            <input type="date" name="date_from" id="date-from" required>
            <span>to</span>
            <input type="date" name="date_to" id="date-to" required>
        </div>

        <!-- Additional Options -->
        <h3>Additional Options</h3>
        <div class="form-group">
            <label><input type="checkbox" name="gps" id="gps"> GPS Navigation ($5/day)</label>
            <label><input type="checkbox" name="child_seat" id="child-seat"> Child Seat ($8/day)</label>
            <label><input type="checkbox" name="additional_driver" id="additional-driver"> Additional Driver ($12/day)</label>
        </div>

        <!-- Rental Summary Section -->
        <div class="summary-section">
            <h3>Summary</h3>
            <p><strong>Car Model:</strong> <span id="selected-car">-</span></p>
            <p><strong>Rental Duration:</strong> <span id="rental-duration">1</span> day(s)</p>
            <p><strong>Base Cost:</strong> $<span id="base-cost">0.00</span></p>
            <p><strong>Additional Fees:</strong> $<span id="additional-fees">0.00</span></p>
            <p><strong>Total Cost:</strong> $<span id="total-cost">0.00</span></p>
        </div>

        <!-- Terms and Conditions -->
        <div class="form-group">
            <label>
                <input type="checkbox" required> I agree to the <a href="#">Terms & Conditions</a>.
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn submit-btn">Reserve Now</button>
    </form>
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
                    <li><a href="./client_booking.php">FAQs</a></li>
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

<script>
function updateCarDetails(select) {
    const selectedOption = select.options[select.selectedIndex];
    const carModel = selectedOption.text;
    const rentPerDay = parseFloat(selectedOption.getAttribute('data-price'));

    // Set default dates to today and today + 1 day
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date-from').value = today;
    document.getElementById('date-to').value = today;

    // Update car model and base cost with per day rental fee
    document.getElementById('selected-car').textContent = carModel;
    document.getElementById('base-cost').textContent = rentPerDay.toFixed(2);

    updateSummary();
}

function updateSummary() {
    const rentPerDay = parseFloat(document.getElementById('base-cost').textContent) || 0;
    const rentalDuration = 1; // Automatically set to 1 day

    // Calculate additional fees based on selected options
    const gpsFee = document.getElementById('gps').checked ? 5 * rentalDuration : 0;
    const childSeatFee = document.getElementById('child-seat').checked ? 8 * rentalDuration : 0;
    const additionalDriverFee = document.getElementById('additional-driver').checked ? 12 * rentalDuration : 0;

    const baseCost = rentPerDay * rentalDuration;
    const additionalFees = gpsFee + childSeatFee + additionalDriverFee;
    const totalCost = baseCost + additionalFees;

    // Update the summary display
    document.getElementById('rental-duration').textContent = rentalDuration;
    document.getElementById('base-cost').textContent = baseCost.toFixed(2);
    document.getElementById('additional-fees').textContent = additionalFees.toFixed(2);
    document.getElementById('total-cost').textContent = totalCost.toFixed(2);
}
</script>

</body>
</html>
