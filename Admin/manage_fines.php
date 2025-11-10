<?php
include '../config.php';
include 'admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fine'])) {
    requireCsrfToken($_POST['csrf_token'] ?? null);
    $fine_id = filter_input(INPUT_POST, 'fine_id', FILTER_VALIDATE_INT);
    if ($fine_id) {
        $deleteStmt = $conn->prepare('DELETE FROM fines WHERE fine_id = ?');
        $deleteStmt->bind_param('i', $fine_id);
        $deleteStmt->execute();
        $deleteStmt->close();
    }
    header('Location: manage_fines.php');
    exit;
}

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
    <link rel="icon" type="image/png" href="../Assets/Images/DriveXpert.png">
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
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
        <h1>Manage Fines</h1>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fine = $finesResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($fine['fine_id']); ?></td>
                                <td><?= htmlspecialchars($fine['customer_name']); ?></td>
                                <td>$<?= number_format((float) $fine['amount'], 2); ?></td>
                                <td><?= htmlspecialchars($fine['reason']); ?></td>
                                <td><?= htmlspecialchars($fine['date_applied']); ?></td>
                                <td>
                                    <!-- Delete Fine Button -->
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                                        <input type="hidden" name="fine_id" value="<?= (int) $fine['fine_id']; ?>">
                                        <button type="submit" name="delete_fine" class="btn1"
                                            onclick="return confirm('Delete this fine?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>

</html>
<?php $conn->close(); ?>