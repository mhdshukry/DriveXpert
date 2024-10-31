<?php
include '../config.php';

// Query to get customer details along with rental and car information
$query = "SELECT c.customer_id, c.name, c.email, c.phone, u.nic_number, c.rental_id, c.car_model, 
                 c.brand AS car_brand, c.date_from, c.date_to, c.total_fees
          FROM customers c
          JOIN users u ON c.customer_id = u.user_id
          WHERE c.rental_id IS NOT NULL";
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
                        <td>$<?= number_format($row['total_fees'], 2) ?></td>
                        <td>
                            <button class="action-btn" onclick="toggleDetails(<?= $row['customer_id'] ?>)">Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9">
                            <div id="details-<?= $row['customer_id'] ?>" class="details-container">
                                <div class="details-section">
                                    <h3>Customer Details</h3>
                                    <p><strong>Name:</strong> <?= $row['name'] ?></p>
                                    <p><strong>Email:</strong> <?= $row['email'] ?></p>
                                    <p><strong>Phone:</strong> <?= $row['phone'] ?></p>
                                    <p><strong>NIC:</strong> <?= $row['nic_number'] ?></p>
                                </div>
                                <div class="details-section">
                                    <h3>Fine Details</h3>
                                    <?php
                                    // Fetch fine details
                                    $fineQuery = "SELECT reason, amount FROM fines WHERE customer_id = {$row['customer_id']}";
                                    $fineResult = $conn->query($fineQuery);
                                    $totalFine = 0;
                                    while ($fine = $fineResult->fetch_assoc()) {
                                        echo "<p><strong>Fine:</strong> $" . number_format($fine['amount'], 2) . " (Reason: " . $fine['reason'] . ")</p>";
                                        $totalFine += $fine['amount'];
                                    }
                                    ?>
                                </div>
                                <div class="details-section">
                                    <h3>Summary</h3>
                                    <p><strong>Total Fine:</strong> $<?= number_format($totalFine, 2) ?></p>
                                    <p><strong>Full Cost:</strong> $<?= number_format($row['total_fees'] + $totalFine, 2) ?></p>
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
function toggleDetails(customerId) {
    var detailsDiv = document.getElementById('details-' + customerId);
    detailsDiv.style.display = detailsDiv.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html>
<?php $conn->close(); ?>
