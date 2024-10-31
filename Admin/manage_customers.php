<?php
include '../config.php';

// Query to get currently renting customers using the `users`, `rentals`, `cars`, and `fines` tables
$query = "
    SELECT u.user_id AS customer_id, r.rental_id, u.name, u.nic_number, u.email, u.phone, 
           c.model AS car_model, c.brand, r.date_from, r.date_to, 
           (r.total_cost + COALESCE(SUM(f.amount), 0)) AS total_fees
    FROM users u
    JOIN rentals r ON u.user_id = r.user_id
    JOIN cars c ON r.car_id = c.car_id
    LEFT JOIN fines f ON f.customer_id = u.user_id
    WHERE r.status = 'confirmed'
    GROUP BY r.rental_id";
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
        .details-container {
            display: none;
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-top: 10px;
            border-radius: 5px;
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
                    <th>Customer ID</th>
                    <th>Rental ID</th>
                    <th>Name</th>
                    <th>NIC No</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Car Model</th>
                    <th>Brand</th>
                    <th>Rental Period</th>
                    <th>Total Fees</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['customer_id'] ?></td>
                        <td><?= $row['rental_id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['nic_number'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= $row['car_model'] ?></td>
                        <td><?= $row['brand'] ?></td>
                        <td><?= $row['date_from'] ?> to <?= $row['date_to'] ?></td>
                        <td>$<?= number_format($row['total_fees'], 2) ?></td>
                        <td>
                            <button class="action-btn" onclick="toggleDetails(<?= $row['customer_id'] ?>)">Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="11">
                            <div id="details-<?= $row['customer_id'] ?>" class="details-container">
                                <?php
                                // Fetch additional details related to fines and options
                                $detailsQuery = "
                                    SELECT f.reason, f.amount AS fine_amount, o.option_name, o.daily_cost
                                    FROM fines f
                                    LEFT JOIN rental_options ro ON ro.rental_id = {$row['rental_id']}
                                    LEFT JOIN additional_options o ON o.option_id = ro.option_id
                                    WHERE f.customer_id = {$row['customer_id']}";
                                $detailsResult = $conn->query($detailsQuery);

                                while ($detail = $detailsResult->fetch_assoc()) {
                                    if ($detail['fine_amount']) {
                                        echo "<p>Fine: $" . number_format($detail['fine_amount'], 2) . " (Reason: " . $detail['reason'] . ")</p>";
                                    }
                                    if ($detail['option_name']) {
                                        echo "<p>Additional Option: " . $detail['option_name'] . " - $" . number_format($detail['daily_cost'], 2) . " per day</p>";
                                    }
                                }
                                ?>
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
