<?php
include '../config.php';

// Query to get active confirmed rental customers
$query = "SELECT r.rental_id, u.name, u.email, u.phone, u.nic_number, r.date_from, r.date_to, 
          r.total_cost, c.model AS car_model, c.brand AS car_brand, c.seat_count, c.max_speed, c.km_per_liter
          FROM rentals r
          JOIN users u ON r.user_id = u.user_id
          JOIN cars c ON r.car_id = c.car_id
          WHERE r.status = 'confirmed'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
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
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_rentals.php">Rentals</a>
        <a href="manage_customers.php" class="active">Customers</a>
        <a href="manage_cars.php">Cars</a>
        <a href="manage_fines.php">Fines</a>
        <a href="reports.php">Reports</a>
    </nav>
    <div class="auth-buttons">
        <button class="btn logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</header>

<main class="main-content">
    <h1>Manage Customers</h1>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Rental ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Car Model</th>
                    <th>Car Brand</th>
                    <th>Rental Period</th>
                    <th>Total Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['rental_id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= $row['car_model'] ?></td>
                        <td><?= $row['car_brand'] ?></td>
                        <td><?= $row['date_from'] ?> to <?= $row['date_to'] ?></td>
                        <td>$<?= number_format($row['total_cost'], 2) ?></td>
                        <td>
                            <button class="action-btn" onclick="toggleDetails(<?= $row['rental_id'] ?>)">Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9">
                            <div id="details-<?= $row['rental_id'] ?>" class="details-container">
                                <div class="details-section">
                                    <h3>Customer Details</h3>
                                    <p><strong>Name:</strong> <?= $row['name'] ?></p>
                                    <p><strong>Email:</strong> <?= $row['email'] ?></p>
                                    <p><strong>Phone:</strong> <?= $row['phone'] ?></p>
                                    <p><strong>NIC:</strong> <?= $row['nic_number'] ?></p>
                                </div>
                                <div class="details-section">
                                    <h3>Car Details</h3>
                                    <p><strong>Model:</strong> <?= $row['car_model'] ?></p>
                                    <p><strong>Brand:</strong> <?= $row['car_brand'] ?></p>
                                    <p><strong>Seat Count:</strong> <?= $row['seat_count'] ?></p>
                                    <p><strong>Max Speed:</strong> <?= $row['max_speed'] ?> km/h</p>
                                    <p><strong>Efficiency:</strong> <?= $row['km_per_liter'] ?> km/l</p>
                                </div>
                                <div class="details-section">
                                    <h3>Fine Details</h3>
                                    <?php
                                    // Fetch fine details
                                    $fineQuery = "SELECT reason, amount FROM fines WHERE customer_id = {$row['rental_id']}";
                                    $fineResult = $conn->query($fineQuery);
                                    $totalFine = 0;
                                    while ($fine = $fineResult->fetch_assoc()) {
                                        echo "<p><strong>Fine:</strong> $" . number_format($fine['amount'], 2) . " (Reason: " . $fine['reason'] . ")</p>";
                                        $totalFine += $fine['amount'];
                                    }
                                    ?>
                                </div>
                                <div class="details-section">
                                    <h3>Additional Options</h3>
                                    <?php
                                    // Fetch additional options details
                                    $optionQuery = "SELECT o.option_name, o.daily_cost
                                                    FROM rental_options ro
                                                    JOIN additional_options o ON o.option_id = ro.option_id
                                                    WHERE ro.rental_id = {$row['rental_id']}";
                                    $optionResult = $conn->query($optionQuery);
                                    $totalOptionCost = 0;
                                    while ($option = $optionResult->fetch_assoc()) {
                                        $daysRented = (strtotime($row['date_to']) - strtotime($row['date_from'])) / 86400;
                                        $optionCost = $option['daily_cost'] * $daysRented;
                                        echo "<p><strong>Option:</strong> " . $option['option_name'] . " - $" . number_format($option['daily_cost'], 2) . " per day (Total: $" . number_format($optionCost, 2) . ")</p>";
                                        $totalOptionCost += $optionCost;
                                    }
                                    ?>
                                </div>
                                <div class="details-section">
                                    <h3>Summary</h3>
                                    <p><strong>Total Fine:</strong> $<?= number_format($totalFine, 2) ?></p>
                                    <p><strong>Total Additional Options Cost:</strong> $<?= number_format($totalOptionCost, 2) ?></p>
                                    <p><strong>Full Cost:</strong> $<?= number_format($row['total_cost'] + $totalFine + $totalOptionCost, 2) ?></p>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="footer-bottom">
    <p>&copy; 2024 DriveXpert Admin Dashboard.</p>
</footer>

<script>
function toggleDetails(rentalId) {
    var detailsDiv = document.getElementById('details-' + rentalId);
    detailsDiv.style.display = detailsDiv.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html>
<?php $conn->close(); ?>
