<?php
include '../config.php';

// Process fine application form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_fine'])) {
    $customer_id = $_POST['customer_id'];
    $amount = $_POST['amount'];
    $reason = $_POST['reason'];

    // Insert fine record into fines table
    $conn->query("INSERT INTO fines (customer_id, amount, reason) VALUES ($customer_id, $amount, '$reason')");
    header("Location: manage_fines.php");
}

// Fetch customers for the dropdown selection in the fine application form
$customersResult = $conn->query("SELECT customer_id, name FROM customers");

// Fetch all fines for displaying fine history
$finesResult = $conn->query("SELECT fines.fine_id, customers.name AS customer_name, fines.amount, fines.reason, fines.date_applied 
                             FROM fines
                             JOIN customers ON fines.customer_id = customers.customer_id
                             ORDER BY fines.date_applied DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fines</title>
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
        <a href="manage_fines.php" class="active">Fines</a>
        <a href="reports.php">Reports</a>
    </nav>
    <div class="auth-buttons">
        <button class="btn logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</header>

<main class="main-content">
    <h1>Manage Fines</h1>

    <!-- Apply Fine Section -->
    <section class="apply-fine-section">
        <h2>Apply Fine</h2>
        <form method="post" class="apply-fine-form">
            <div class="form-group">
                <label for="customer_id">Select Customer</label>
                <select name="customer_id" id="customer_id" required>
                    <option value="">Choose a Customer</option>
                    <?php while ($customer = $customersResult->fetch_assoc()) { ?>
                        <option value="<?= $customer['customer_id'] ?>"><?= $customer['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
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
    </section>

    <!-- Fine History Section -->
    <section class="fine-history-section">
        <h2>Fine History</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fine ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Date Applied</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fine = $finesResult->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $fine['fine_id'] ?></td>
                            <td><?= $fine['customer_name'] ?></td>
                            <td>$<?= number_format($fine['amount'], 2) ?></td>
                            <td><?= $fine['reason'] ?></td>
                            <td><?= $fine['date_applied'] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>
