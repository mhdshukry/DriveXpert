<?php
include '../config.php';
include 'admin_guard.php';
require_once __DIR__ . '/rental_detail_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken($_POST['csrf_token'] ?? null);
    $action = $_POST['action'] ?? '';

    if ($action === 'confirm') {
        $rentalId = filter_input(INPUT_POST, 'rental_id', FILTER_VALIDATE_INT);
        if (!$rentalId) {
            $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Invalid rental identifier.'];
            header('Location: confirm_rental.php');
            exit;
        }

        try {
            $conn->begin_transaction();

            $updateRental = $conn->prepare("UPDATE rentals SET status = 'confirmed' WHERE rental_id = ?");
            $updateRental->bind_param('i', $rentalId);
            if (!$updateRental->execute()) {
                throw new RuntimeException('Failed to update rental status.');
            }

            $updateCar = $conn->prepare('UPDATE cars SET availability = 0 WHERE car_id = (SELECT car_id FROM rentals WHERE rental_id = ?)');
            $updateCar->bind_param('i', $rentalId);
            if (!$updateCar->execute()) {
                throw new RuntimeException('Failed to update car availability.');
            }

            $tempCustomer = $conn->prepare("
                INSERT INTO customers (customer_id, name, email, phone, rental_id, car_model, brand, date_from, date_to, total_fees)
                SELECT u.user_id, u.name, u.email, u.phone, r.rental_id, c.model, c.brand, r.date_from, r.date_to, r.total_cost
                FROM rentals r
                JOIN users u ON r.user_id = u.user_id
                JOIN cars c ON r.car_id = c.car_id
                WHERE r.rental_id = ?
                ON DUPLICATE KEY UPDATE 
                    car_model = VALUES(car_model),
                    brand = VALUES(brand),
                    date_from = VALUES(date_from),
                    date_to = VALUES(date_to),
                    total_fees = VALUES(total_fees)
            ");
            $tempCustomer->bind_param('i', $rentalId);
            if (!$tempCustomer->execute()) {
                throw new RuntimeException('Failed to stage customer data.');
            }

            $updateRental->close();
            $updateCar->close();
            $tempCustomer->close();

            $conn->commit();
            $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Rental confirmed successfully.'];
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('Confirm rental failed: ' . $e->getMessage());
            $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Unable to confirm rental. Please try again.'];
        }

        header('Location: confirm_rental.php');
        exit;
    }

    if ($action === 'complete') {
        $rentalId = filter_input(INPUT_POST, 'rental_id', FILTER_VALIDATE_INT);
        $carId = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
        if (!$rentalId || !$carId) {
            $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Invalid completion request.'];
            header('Location: confirm_rental.php');
            exit;
        }

        try {
            $conn->begin_transaction();

            $detailsStmt = $conn->prepare("
                SELECT r.rental_id, u.user_id AS customer_id, r.date_from, r.date_to, r.total_cost, c.model AS car_model,
                       c.brand AS car_brand, c.seat_count, c.max_speed, c.km_per_liter
                FROM rentals r
                JOIN users u ON r.user_id = u.user_id
                JOIN cars c ON r.car_id = c.car_id
                WHERE r.rental_id = ?
            ");
            $detailsStmt->bind_param('i', $rentalId);
            if (!$detailsStmt->execute()) {
                throw new RuntimeException('Failed to load rental details.');
            }
            $detailsResult = $detailsStmt->get_result();
            $details = $detailsResult->fetch_assoc();
            $detailsStmt->close();

            if (!$details) {
                throw new RuntimeException('Rental not found for completion.');
            }

            $fineStmt = $conn->prepare('SELECT COALESCE(SUM(amount), 0) AS total_fine FROM fines WHERE rental_id = ?');
            $fineStmt->bind_param('i', $rentalId);
            if (!$fineStmt->execute()) {
                throw new RuntimeException('Failed to aggregate fines.');
            }
            $fineResult = $fineStmt->get_result()->fetch_assoc();
            $fineStmt->close();

            $totalFine = (float) ($fineResult['total_fine'] ?? 0);
            $totalFeesWithFines = (float) $details['total_cost'] + $totalFine;
            $fineDetails = 'Total Fine: $' . number_format($totalFine, 2);

            $historyStmt = $conn->prepare("
                INSERT INTO rental_history (rental_id, customer_id, date_from, date_to, total_fees, car_model, car_brand,
                                            seat_count, max_speed, km_per_liter, fine_details, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $rentalHistoryId = (int) $details['rental_id'];
            $customerId = (int) $details['customer_id'];
            $seatCount = (int) $details['seat_count'];
            $maxSpeed = (int) $details['max_speed'];
            $kmPerLiter = (float) $details['km_per_liter'];
            $historyStmt->bind_param(
                'iissdssiids',
                $rentalHistoryId,
                $customerId,
                $details['date_from'],
                $details['date_to'],
                $totalFeesWithFines,
                $details['car_model'],
                $details['car_brand'],
                $seatCount,
                $maxSpeed,
                $kmPerLiter,
                $fineDetails
            );
            if (!$historyStmt->execute()) {
                throw new RuntimeException('Failed to record rental history.');
            }
            $historyStmt->close();

            $completeRental = $conn->prepare("UPDATE rentals SET status = 'completed' WHERE rental_id = ?");
            $completeRental->bind_param('i', $rentalId);
            if (!$completeRental->execute()) {
                throw new RuntimeException('Failed to mark rental as completed.');
            }
            $completeRental->close();

            $resetCar = $conn->prepare('UPDATE cars SET availability = 1 WHERE car_id = ?');
            $resetCar->bind_param('i', $carId);
            if (!$resetCar->execute()) {
                throw new RuntimeException('Failed to reset car availability.');
            }
            $resetCar->close();

            $cleanupCustomer = $conn->prepare('DELETE FROM customers WHERE rental_id = ?');
            $cleanupCustomer->bind_param('i', $rentalId);
            if (!$cleanupCustomer->execute()) {
                throw new RuntimeException('Failed to clean up staged customer data.');
            }
            $cleanupCustomer->close();

            $conn->commit();
            $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Rental marked as completed.'];
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('Complete rental failed: ' . $e->getMessage());
            $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Unable to complete rental.'];
        }

        header('Location: confirm_rental.php');
        exit;
    }
}

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$rentalQuery = "SELECT r.rental_id, r.status, r.date_from, r.date_to, r.total_cost, r.created_at,
                       r.car_id,
                       u.user_id, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone, u.nic_number,
                       c.brand AS car_brand, c.model AS car_model, c.seat_count, c.max_speed, c.km_per_liter, c.rent_per_day
                FROM rentals r
                JOIN users u ON r.user_id = u.user_id
                JOIN cars c ON r.car_id = c.car_id
                WHERE r.status = 'confirmed'";
$rentalResult = $conn->query($rentalQuery);

$confirmedRentals = [];
if ($rentalResult instanceof mysqli_result) {
    while ($row = $rentalResult->fetch_assoc()) {
        $confirmedRentals[] = $row;
    }
    $rentalResult->free();
}

$rentalIds = array_column($confirmedRentals, 'rental_id');
$supplementary = collectSupplementaryRentalData($conn, $rentalIds);

$currencyMeta = ['symbol' => '$', 'code' => 'USD'];
foreach ($confirmedRentals as $index => $row) {
    $confirmedRentals[$index]['detail_payload'] = buildRentalDetailPayload(
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
    <title>Confirmed Rentals</title>
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
    <h1>Confirmed Rentals</h1>
    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success'; ?>">
            <?= htmlspecialchars($flash['text']); ?>
        </div>
    <?php endif; ?>
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
                <?php if (empty($confirmedRentals)): ?>
                <tr>
                    <td colspan="6" class="empty-row">No confirmed rentals awaiting completion.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($confirmedRentals as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['rental_id']); ?></td>
                        <td><?= htmlspecialchars($row['customer_name']); ?></td>
                        <td><?= htmlspecialchars($row['car_model']); ?></td>
                        <td><?= htmlspecialchars($row['date_from'] . ' to ' . $row['date_to']); ?></td>
                        <td><?= htmlspecialchars(ucfirst($row['status'])); ?></td>
                        <td>
                            <button type="button" class="action-btn secondary js-view-booking"
                                data-rental-details='<?= htmlspecialchars(json_encode($row['detail_payload'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>'>View</button>
                            <form method="post" class="inline-form" action="confirm_rental.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="rental_id" value="<?= (int) $row['rental_id']; ?>">
                                <input type="hidden" name="car_id" value="<?= (int) $row['car_id']; ?>">
                                <button type="submit" class="action-btn" onclick="return confirm('Mark this rental as completed?');">Complete</button>
                            </form>
                            <a class="action-btn secondary" href="fine.php?rental_id=<?= (int) $row['rental_id']; ?>">Fine</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<script src="../Assets/JS/rental-detail-modal.js?v=<?= $modalScriptVersion; ?>"></script>
</body>
</html>
<?php $conn->close(); ?>
