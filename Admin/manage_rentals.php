<?php
include '../config.php';
include 'admin_guard.php';
require_once __DIR__ . '/rental_detail_helpers.php';

// Query to get pending rental orders with enriched customer and vehicle data
$rentalQuery = "SELECT r.rental_id, r.status, r.date_from, r.date_to, r.total_cost, r.created_at,
                       r.car_id,
                       u.user_id, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone, u.nic_number,
                       c.brand AS car_brand, c.model AS car_model, c.seat_count, c.max_speed, c.km_per_liter, c.rent_per_day
                FROM rentals r
                JOIN users u ON r.user_id = u.user_id
                JOIN cars c ON r.car_id = c.car_id
                WHERE r.status = 'pending'";
$rentalResult = $conn->query($rentalQuery);

$pendingRentals = [];
if ($rentalResult instanceof mysqli_result) {
    while ($row = $rentalResult->fetch_assoc()) {
        $pendingRentals[] = $row;
    }
    $rentalResult->free();
}

$rentalIds = array_column($pendingRentals, 'rental_id');
$supplementary = collectSupplementaryRentalData($conn, $rentalIds);

$currencyMeta = ['symbol' => '$', 'code' => 'USD'];
foreach ($pendingRentals as $index => $row) {
    $pendingRentals[$index]['detail_payload'] = buildRentalDetailPayload(
        $row,
        $supplementary['extras'],
        $supplementary['insurance'],
        $supplementary['fines'],
        $currencyMeta
    );
}

$modalScriptPath = __DIR__ . '/../Assets/JS/rental-detail-modal.js';
$modalScriptVersion = file_exists($modalScriptPath) ? (string) filemtime($modalScriptPath) : (string) time();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rentals</title>
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
                    <?php if (empty($pendingRentals)): ?>
                        <tr>
                            <td colspan="6" class="empty-row">No pending rentals at the moment.</td>
                        </tr>
                    <?php else:
                        foreach ($pendingRentals as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['rental_id']); ?></td>
                                <td><?= htmlspecialchars($row['customer_name']); ?></td>
                                <td><?= htmlspecialchars($row['car_model']); ?></td>
                                <td><?= htmlspecialchars($row['date_from'] . ' to ' . $row['date_to']); ?></td>
                                <td><?= htmlspecialchars(ucfirst($row['status'])); ?></td>
                                <td>
                                    <button type="button" class="action-btn secondary js-view-booking"
                                        data-rental-details='<?= htmlspecialchars(json_encode($row['detail_payload'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>'>View</button>
                                    <form method="post" action="confirm_rental.php" class="inline-form">
                                        <input type="hidden" name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <input type="hidden" name="rental_id" value="<?= (int) $row['rental_id']; ?>">
                                        <button type="submit" class="action-btn"
                                            onclick="return confirm('Confirm this rental request?');">Confirm</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="../Assets/JS/rental-detail-modal.js?v=<?= $modalScriptVersion; ?>"></script>
</body>

</html>
<?php $conn->close(); ?>