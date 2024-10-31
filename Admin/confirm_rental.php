<?php
include '../config.php';

// Confirm rental if action is "confirm"
if (isset($_GET['action']) && $_GET['action'] === 'confirm' && isset($_GET['id'])) {
    $rental_id = $_GET['id'];

    // Update rental status to 'confirmed' and set car availability to not available (0)
    $conn->query("UPDATE rentals SET status = 'confirmed' WHERE rental_id = $rental_id");
    $conn->query("UPDATE cars SET availability = 0 WHERE car_id = (SELECT car_id FROM rentals WHERE rental_id = $rental_id)");

    // Insert customer rental details into the customers table
    $customerQuery = "
        UPDATE customers c
        JOIN rentals r ON c.customer_id = r.user_id
        JOIN cars car ON r.car_id = car.car_id
        SET c.rental_id = r.rental_id, 
            c.car_model = car.model, 
            c.brand = car.brand, 
            c.date_from = r.date_from, 
            c.date_to = r.date_to, 
            c.total_fees = r.total_cost
        WHERE r.rental_id = $rental_id
    ";
    $conn->query($customerQuery);
}

// Query to get confirmed rentals
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
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_rentals.php">Rentals</a>
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
    let fineAmount = prompt("Enter fine amount:");
    if (fineAmount) {
        window.location.href = `confirm_rental.php?action=fine&rental_id=${rentalId}&amount=${fineAmount}`;
    }
}
</script>

</body>
</html>

<?php
// Handle completion and fine actions
if (isset($_GET['action'])) {
    $rental_id = $_GET['rental_id'] ?? null;
    $car_id = $_GET['car_id'] ?? null;

    // Complete the rental
    if ($_GET['action'] === 'complete' && $rental_id && $car_id) {
        $conn->query("UPDATE rentals SET status = 'completed' WHERE rental_id = $rental_id");
        $conn->query("UPDATE cars SET availability = 1 WHERE car_id = $car_id");

        // Reset rental-related fields in `customers` table
        $conn->query("UPDATE customers SET rental_id = NULL, car_model = NULL, brand = NULL, date_from = NULL, date_to = NULL, total_fees = 0 WHERE rental_id = $rental_id");

        header("Location: confirm_rental.php");
        exit();
    }

    // Apply fine
    if ($_GET['action'] === 'fine' && $rental_id && isset($_GET['amount'])) {
        $amount = $_GET['amount'];
        $conn->query("INSERT INTO fines (rental_id, amount, date_applied) VALUES ($rental_id, $amount, NOW())");

        // Update total fees in `customers` table to reflect the fine
        $conn->query("UPDATE customers SET total_fees = total_fees + $amount WHERE rental_id = $rental_id");

        header("Location: confirm_rental.php");
        exit();
    }
}
$conn->close();
