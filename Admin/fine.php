<?php
include '../config.php';

// Check if rental ID is provided for applying a fine
if (isset($_GET['rental_id'])) {
    $rental_id = $_GET['rental_id'];

    // Fetch customer details based on the rental_id
    $customerQuery = "
        SELECT u.name AS customer_name, r.user_id AS customer_id, r.date_from, r.date_to
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.rental_id = $rental_id
    ";
    $customerResult = $conn->query($customerQuery);

    if ($customerResult->num_rows > 0) {
        $customer = $customerResult->fetch_assoc();
        $customer_id = $customer['customer_id'];
        $customer_name = $customer['customer_name'];
        $rental_period = $customer['date_from'] . ' to ' . $customer['date_to'];
    } else {
        echo "Error: Rental ID not found.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_fine'])) {
    $amount = $_POST['amount'];
    $reason = $_POST['reason'];

    // Validate that customer ID exists in the customers table
    $customerCheck = $conn->query("SELECT 1 FROM customers WHERE customer_id = $customer_id");
    if ($customerCheck->num_rows > 0) {
        // Insert fine record if customer_id exists
        $conn->query("INSERT INTO fines (rental_id, customer_id, amount, reason) VALUES ($rental_id, $customer_id, $amount, '$reason')");
        header("Location: manage_fines.php");
    } else {
        echo "Error: Customer ID does not exist in customers table.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Fine</title>
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
    <link rel="stylesheet" href="../Assets/CSS/fine.css">
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
    <h1>Apply Fine</h1>
    <div class="form-container">
        <form method="post" class="styled-form">
            <!-- Customer Details -->
            <div class="form-group">
                <label for="customer_name">Customer</label>
                <input type="text" id="customer_name" value="<?= $customer_name; ?>" readonly>
            </div>

            <div class="form-group">
                <label for="rental_id">Rental ID</label>
                <input type="text" id="rental_id" value="<?= $rental_id; ?>" readonly>
            </div>

            <div class="form-group">
                <label for="rental_period">Rental Period</label>
                <input type="text" id="rental_period" value="<?= $rental_period; ?>" readonly>
            </div>

            <!-- Fine Details -->
            <div class="form-group">
                <label for="amount">Fine Amount</label>
                <input type="number" name="amount" id="amount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" rows="3" required></textarea>
            </div>
            
            <button type="submit" name="apply_fine" class="btn">Apply Fine</button>
        </form>
    </div>
</main>


</body>
</html>
