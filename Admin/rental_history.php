<?php
include '../config.php';

// Query to get completed rental history
$historyQuery = "SELECT h.history_id, h.rental_id, u.name AS customer_name, u.email AS customer_email, 
                 u.phone AS customer_phone, h.date_from, h.date_to, h.total_fees, h.car_model, 
                 h.car_brand, h.seat_count, h.max_speed, h.km_per_liter, h.fine_details, h.additional_options 
                 FROM rental_history h
                 JOIN users u ON h.customer_id = u.user_id";
$historyResult = $conn->query($historyQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental History</title>
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
    <style>

        /* Professional styling for the details toggle container */
        .details-container {
            display: none;
            background-color: #F7F9FB;
            padding: 15px;
            border-left: 4px solid #2980B9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            margin-top: 10px;
            font-size: 14px;
            color: #2C3E50;
        }
        .details-container h3 {
            font-size: 16px;
            color: #2980B9;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .details-container p {
            margin: 5px 0;
        }
        .details-container p strong {
            color: #34495E;
        }

        /* Styling individual detail sections within the toggle */
        .details-section {
            margin-bottom: 15px;
        }
        .details-section:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>

<header class="header">
    <div class="logo">
        <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
    </div>
    <nav class="nav-links">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        
        <!-- Rentals Dropdown -->
        <div class="dropdown">
            <a class="dropdown-toggle">Rentals</a>
            <div class="dropdown-menu">
                <a href="confirm_rental.php">Confirm Rental</a>
                <a href="rental_history.php">Rental History</a>
                <a href="manage_rentals.php">Manage Rental</a>
            </div>
        </div>
        
        <a href="manage_customers.php">Customers</a>
        <a href="manage_cars.php">Cars</a>
        <a href="manage_fines.php">Fines</a>
        <a href="reports.php">Reports</a>
    </nav>
    <div class="auth-buttons">
        <button class="btn logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</header>

<main class="main-content">
    <h1>Rental History</h1>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Rental ID</th>
                    <th>Customer Name</th>
                    <th>Car Model</th>
                    <th>Rental Period</th>
                    <th>Total Fees</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $historyResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['rental_id']; ?></td>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td><?php echo $row['car_model']; ?></td>
                    <td><?php echo $row['date_from'] . ' to ' . $row['date_to']; ?></td>
                    <td>$<?php echo number_format($row['total_fees'], 2); ?></td>
                    <td>
                        <button class="action-btn" onclick="toggleDetails(<?php echo $row['history_id']; ?>)">Details</button>
                        <a href="generate_invoice_image.php?history_id=<?php echo $row['history_id']; ?>" class="action-btn">Download JPG</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <div id="details-<?php echo $row['history_id']; ?>" class="details-container">
                            <div class="details-section">
                                <h3>Customer Details</h3>
                                <p><strong>Name:</strong> <?php echo $row['customer_name']; ?></p>
                                <p><strong>Email:</strong> <?php echo $row['customer_email']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $row['customer_phone']; ?></p>
                            </div>
                            <div class="details-section">
                                <h3>Car Details</h3>
                                <p><strong>Brand:</strong> <?php echo $row['car_brand']; ?></p>
                                <p><strong>Model:</strong> <?php echo $row['car_model']; ?></p>
                                <p><strong>Seats:</strong> <?php echo $row['seat_count']; ?></p>
                                <p><strong>Max Speed:</strong> <?php echo $row['max_speed']; ?> km/h</p>
                                <p><strong>Efficiency:</strong> <?php echo $row['km_per_liter']; ?> km/l</p>
                            </div>
                            <?php if (!empty($row['fine_details'])): ?>
                            <div class="details-section">
                                <h3>Fine Details</h3>
                                <p><?php echo $row['fine_details']; ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($row['additional_options'])): ?>
                            <div class="details-section">
                                <h3>Additional Options</h3>
                                <p><?php echo $row['additional_options']; ?></p>
                            </div>
                            <?php endif; ?>
                            <div class="details-section">
                                <h3>Total Fees</h3>
                                <p><strong>Total Fees:</strong> $<?php echo number_format($row['total_fees'], 2); ?></p>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function toggleDetails(historyId) {
    var detailsDiv = document.getElementById('details-' + historyId);
    detailsDiv.style.display = detailsDiv.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html>
<?php $conn->close(); ?>
