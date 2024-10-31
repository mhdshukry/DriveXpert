<?php
include '../config.php';

// Query to get pending rental orders
$rentalQuery = "SELECT rentals.rental_id, users.name AS customer, cars.model AS car, 
                rentals.date_from, rentals.date_to, rentals.status 
                FROM rentals 
                JOIN users ON rentals.user_id = users.user_id 
                JOIN cars ON rentals.car_id = cars.car_id 
                WHERE rentals.status = 'pending'";
$rentalResult = $conn->query($rentalQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rentals</title>
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
</head>
<body>

<header class="header">
    <div class="logo">
        <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
    </div>
    <nav class="nav-links">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_rentals.php" class="active">Rentals</a>
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
    <h1>Pending Rentals</h1>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Rental ID</th>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Rental Period</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $rentalResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['rental_id']; ?></td>
                    <td><?php echo $row['customer']; ?></td>
                    <td><?php echo $row['car']; ?></td>
                    <td><?php echo $row['date_from'] . ' to ' . $row['date_to']; ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td>
                        <button class="action-btn" onclick="confirmRental(<?php echo $row['rental_id']; ?>)">Confirm</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function confirmRental(rentalId) {
    if (confirm("Are you sure you want to confirm this rental?")) {
        window.location.href = `confirm_rental.php?id=${rentalId}&action=confirm`;
    }
}
</script>

</body>
</html>
<?php $conn->close(); ?>
