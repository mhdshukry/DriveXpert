<?php
session_start();
include '../config.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view your bookings.'); window.location.href='../auth.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch pending bookings
$pendingBookingsQuery = "SELECT r.*, c.brand, c.model FROM rentals r 
    JOIN cars c ON r.car_id = c.car_id 
    WHERE r.user_id = ? AND r.status = 'pending'";
$pendingStmt = $conn->prepare($pendingBookingsQuery);
$pendingStmt->bind_param("i", $user_id);
$pendingStmt->execute();
$pendingPendingResults = $pendingStmt->get_result();

// Fetch confirmed bookings
$confirmedBookingsQuery = "SELECT cr.*, c.brand, c.model, f.amount as fine_amount, f.reason as fine_reason 
    FROM confirmed_rentals cr 
    JOIN cars c ON cr.car_id = c.car_id 
    LEFT JOIN fines f ON cr.rental_id = f.rental_id 
    WHERE cr.user_id = ? AND cr.status = 'confirmed'";
$confirmedStmt = $conn->prepare($confirmedBookingsQuery);
$confirmedStmt->bind_param("i", $user_id);
$confirmedStmt->execute();
$confirmedResults = $confirmedStmt->get_result();

// Fetch completed bookings
$completedBookingsQuery = "SELECT rh.*, f.amount as fine_amount, f.reason as fine_reason 
    FROM rental_history rh 
    LEFT JOIN fines f ON rh.rental_id = f.rental_id 
    WHERE rh.customer_id = ?";
$completedStmt = $conn->prepare($completedBookingsQuery);
$completedStmt->bind_param("i", $user_id);
$completedStmt->execute();
$completedResults = $completedStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Bookings - DriveXpert</title>
    <link rel="stylesheet" href="../Assets/CSS/booking.css">
</head>
<body>

<div class="main-content">
    <h1>Your Bookings</h1>

    <!-- Pending Bookings -->
    <section class="booking-section">
        <h2>Pending Bookings</h2>
        <?php if ($pendingPendingResults->num_rows > 0): ?>
            <?php while ($row = $pendingPendingResults->fetch_assoc()): ?>
                <div class="booking-card">
                    <h3><?= $row['brand'] . " " . $row['model'] ?></h3>
                    <p><strong>Rental Dates:</strong> <?= $row['date_from'] ?> to <?= $row['date_to'] ?></p>
                    <p><strong>Total Cost:</strong> $<?= number_format($row['total_cost'], 2) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No pending bookings.</p>
        <?php endif; ?>
    </section>

    <!-- Confirmed Bookings -->
    <section class="booking-section">
        <h2>Confirmed Bookings</h2>
        <?php if ($confirmedResults->num_rows > 0): ?>
            <?php while ($row = $confirmedResults->fetch_assoc()): ?>
                <div class="booking-card">
                    <h3><?= $row['brand'] . " " . $row['model'] ?></h3>
                    <p><strong>Rental Dates:</strong> <?= $row['date_from'] ?> to <?= $row['date_to'] ?></p>
                    <p><strong>Total Cost:</strong> $<?= number_format($row['total_cost'], 2) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
                    <?php if ($row['fine_amount'] > 0): ?>
                        <button class="toggle-button" onclick="toggleDetails('fine-<?= $row['rental_id'] ?>')">Show Fine Details</button>
                        <div id="fine-<?= $row['rental_id'] ?>" class="details-container">
                            <p><strong>Fine Amount:</strong> $<?= number_format($row['fine_amount'], 2) ?></p>
                            <p><strong>Reason:</strong> <?= $row['fine_reason'] ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No confirmed bookings.</p>
        <?php endif; ?>
    </section>

    <!-- Completed Bookings -->
    <section class="booking-section">
        <h2>Completed Bookings</h2>
        <?php if ($completedResults->num_rows > 0): ?>
            <?php while ($row = $completedResults->fetch_assoc()): ?>
                <div class="booking-card">
                    <h3><?= $row['car_brand'] . " " . $row['car_model'] ?></h3>
                    <p><strong>Rental Dates:</strong> <?= $row['date_from'] ?> to <?= $row['date_to'] ?></p>
                    <p><strong>Total Fees:</strong> $<?= number_format($row['total_fees'], 2) ?></p>
                    <?php if ($row['fine_amount'] > 0): ?>
                        <button class="toggle-button" onclick="toggleDetails('fine-<?= $row['rental_id'] ?>')">Show Fine Details</button>
                        <div id="fine-<?= $row['rental_id'] ?>" class="details-container">
                            <p><strong>Fine Amount:</strong> $<?= number_format($row['fine_amount'], 2) ?></p>
                            <p><strong>Reason:</strong> <?= $row['fine_reason'] ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No completed bookings.</p>
        <?php endif; ?>
    </section>
</div>

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

<script>
// Toggle the visibility of additional details (fine details)
function toggleDetails(id) {
    const details = document.getElementById(id);
    if (details.style.display === "none" || !details.style.display) {
        details.style.display = "block";
    } else {
        details.style.display = "none";
    }
}
</script>
</body>
</html>

<?php
// Close all statements and connections
$pendingStmt->close();
$confirmedStmt->close();
$completedStmt->close();
$conn->close();
?>
