<?php
include '../config.php';

if (isset($_GET['action']) && $_GET['action'] === 'confirm' && isset($_GET['id'])) {
    $rental_id = $_GET['id'];

    // Confirm the rental and update car availability
    $conn->query("UPDATE rentals SET status = 'confirmed' WHERE rental_id = $rental_id") or die($conn->error);
    $conn->query("UPDATE cars SET availability = 0 WHERE car_id = (SELECT car_id FROM rentals WHERE rental_id = $rental_id)") or die($conn->error);

    // Temporarily store customer rental details in customers table
    $tempCustomerQuery = "
        INSERT INTO customers (customer_id, name, email, phone, rental_id, car_model, brand, date_from, date_to, total_fees)
        SELECT u.user_id, u.name, u.email, u.phone, r.rental_id, c.model, c.brand, r.date_from, r.date_to, r.total_cost
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN cars c ON r.car_id = c.car_id
        WHERE r.rental_id = $rental_id
        ON DUPLICATE KEY UPDATE 
            car_model = VALUES(car_model), brand = VALUES(brand), date_from = VALUES(date_from), date_to = VALUES(date_to), total_fees = VALUES(total_fees)
    ";
    $conn->query($tempCustomerQuery) or die($conn->error);
}

if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['rental_id']) && isset($_GET['car_id'])) {
    $rental_id = $_GET['rental_id'];
    $car_id = $_GET['car_id'];

    // Fetch rental details for history insertion
    $rentalDetails = $conn->query("
        SELECT r.rental_id, u.user_id AS customer_id, r.date_from, r.date_to, r.total_cost, c.model AS car_model, 
               c.brand AS car_brand, c.seat_count, c.max_speed, c.km_per_liter
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN cars c ON r.car_id = c.car_id
        WHERE r.rental_id = $rental_id
    ")->fetch_assoc();

    // Calculate total fines for the rental
    $fineResult = $conn->query("SELECT SUM(amount) AS total_fine FROM fines WHERE rental_id = $rental_id");
    $totalFine = $fineResult->fetch_assoc()['total_fine'] ?? 0;

    // Calculate total fees including fines
    $totalFeesWithFines = $rentalDetails['total_cost'] + $totalFine;

    // Insert completed rental into rental_history
    $conn->query("
        INSERT INTO rental_history (rental_id, customer_id, date_from, date_to, total_fees, car_model, car_brand, 
                                    seat_count, max_speed, km_per_liter, fine_details, created_at)
        VALUES (
            {$rentalDetails['rental_id']}, {$rentalDetails['customer_id']}, '{$rentalDetails['date_from']}', 
            '{$rentalDetails['date_to']}', $totalFeesWithFines, '{$rentalDetails['car_model']}', 
            '{$rentalDetails['car_brand']}', {$rentalDetails['seat_count']}, {$rentalDetails['max_speed']}, 
            {$rentalDetails['km_per_liter']}, 'Total Fine: $$totalFine', NOW()
        )
    ");

    // Complete the rental and reset car availability
    $conn->query("UPDATE rentals SET status = 'completed' WHERE rental_id = $rental_id") or die($conn->error);
    $conn->query("UPDATE cars SET availability = 1 WHERE car_id = $car_id") or die($conn->error);

    // Remove temporary data in customers table for this rental
    $conn->query("DELETE FROM customers WHERE rental_id = $rental_id") or die($conn->error);

    header("Location: confirm_rental.php");
    exit();
}

// Display confirmed rentals
$rentalQuery = "SELECT rentals.rental_id, users.name AS customer, cars.model AS car, 
                rentals.date_from, rentals.date_to, rentals.status, rentals.car_id
                FROM rentals 
                JOIN users ON rentals.user_id = users.user_id 
                JOIN cars ON rentals.car_id = cars.car_id 
                WHERE rentals.status = 'confirmed'";
$rentalResult = $conn->query($rentalQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmed Rentals</title>
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
    </div>
    <nav class="nav-links">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
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
    <h1>Confirmed Rentals</h1>
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
                        <button class="action-btn" onclick="completeRental(<?php echo $row['rental_id']; ?>, <?php echo $row['car_id']; ?>)">Complete</button>
                        <button class="action-btn" onclick="applyFine(<?php echo $row['rental_id']; ?>)">Fine</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function completeRental(rentalId, carId) {
    if (confirm("Mark this rental as complete?")) {
        window.location.href = `confirm_rental.php?action=complete&rental_id=${rentalId}&car_id=${carId}`;
    }
}

function applyFine(rentalId) {
    window.location.href = `fine.php?rental_id=${rentalId}`;
}
</script>

</body>
</html>

<?php $conn->close(); ?>
