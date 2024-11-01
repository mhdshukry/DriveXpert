<?php
session_start();
include '../config.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view your bookings.'); window.location.href='../auth.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all bookings based on status and merge into one array
$allBookings = [];

// Pending bookings from `rentals` table
$pendingQuery = "SELECT r.rental_id, r.date_from, r.date_to, r.total_cost, 'pending' AS status, c.brand, c.model, NULL AS fine_amount, NULL AS fine_reason
    FROM rentals r 
    JOIN cars c ON r.car_id = c.car_id 
    WHERE r.user_id = ? AND r.status = 'pending'";
$pendingStmt = $conn->prepare($pendingQuery);
$pendingStmt->bind_param("i", $user_id);
$pendingStmt->execute();
$allBookings = array_merge($allBookings, $pendingStmt->get_result()->fetch_all(MYSQLI_ASSOC));

// Confirmed bookings from `confirmed_rentals` table
$confirmedQuery = "SELECT cr.rental_id, cr.date_from, cr.date_to, cr.total_cost, 'confirmed' AS status, c.brand, c.model, f.amount AS fine_amount, f.reason AS fine_reason
    FROM confirmed_rentals cr
    JOIN cars c ON cr.car_id = c.car_id 
    LEFT JOIN fines f ON cr.rental_id = f.rental_id 
    WHERE cr.user_id = ? AND cr.status = 'confirmed'";
$confirmedStmt = $conn->prepare($confirmedQuery);
$confirmedStmt->bind_param("i", $user_id);
$confirmedStmt->execute();
$allBookings = array_merge($allBookings, $confirmedStmt->get_result()->fetch_all(MYSQLI_ASSOC));

// Completed bookings from `rental_history` table
$completedQuery = "SELECT rh.rental_id, rh.date_from, rh.date_to, rh.total_fees AS total_cost, 'completed' AS status, rh.car_brand AS brand, rh.car_model AS model, f.amount AS fine_amount, f.reason AS fine_reason
    FROM rental_history rh
    LEFT JOIN fines f ON rh.rental_id = f.rental_id 
    WHERE rh.customer_id = ?";
$completedStmt = $conn->prepare($completedQuery);
$completedStmt->bind_param("i", $user_id);
$completedStmt->execute();
$allBookings = array_merge($allBookings, $completedStmt->get_result()->fetch_all(MYSQLI_ASSOC));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Bookings - DriveXpert</title>
    <link rel="stylesheet" href="../Assets/CSS/booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sofia&family=Tangerine:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">

    <style>
        /* Table styling */
        .table-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 250px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .toggle-button {
            cursor: pointer;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        .toggle-button:hover {
            background-color: #0056b3;
        }
        .details-container {
            display: none;
            padding: 15px;
            background-color: #f1f1f1;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
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

<div class="main-content">
    <h1>Your Bookings</h1>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Rental ID</th>
                    <th>Car Model</th>
                    <th>Rental Dates</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allBookings as $booking): ?>
                <tr>
                    <td><?= $booking['rental_id'] ?></td>
                    <td><?= $booking['brand'] . ' ' . $booking['model'] ?></td>
                    <td><?= $booking['date_from'] . ' to ' . $booking['date_to'] ?></td>
                    <td>$<?= number_format($booking['total_cost'], 2) ?></td>
                    <td><?= ucfirst($booking['status']) ?></td>
                    <td>
                        <button class="toggle-button" onclick="toggleDetails('details-<?= $booking['rental_id'] ?>')">Details</button>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <div id="details-<?= $booking['rental_id'] ?>" class="details-container">
                            <h3>Booking Details</h3>
                            <p><strong>Car:</strong> <?= $booking['brand'] . ' ' . $booking['model'] ?></p>
                            <p><strong>Rental Period:</strong> <?= $booking['date_from'] ?> to <?= $booking['date_to'] ?></p>
                            <p><strong>Total Cost:</strong> $<?= number_format($booking['total_cost'], 2) ?></p>
                            <p><strong>Status:</strong> <?= ucfirst($booking['status']) ?></p>
                            <?php if (!empty($booking['fine_amount'])): ?>
                                <h4>Fine Details</h4>
                                <p><strong>Fine Amount:</strong> $<?= number_format($booking['fine_amount'], 2) ?></p>
                                <p><strong>Reason:</strong> <?= $booking['fine_reason'] ?></p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
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
// Toggle the visibility of additional details
function toggleDetails(id) {
    const details = document.getElementById(id);
    details.style.display = details.style.display === "none" || !details.style.display ? "block" : "none";
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
