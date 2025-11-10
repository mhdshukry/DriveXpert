<?php
include '../config.php';
include 'admin_guard.php';

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$rentalId = filter_input(INPUT_GET, 'rental_id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken($_POST['csrf_token'] ?? null);
    $rentalId = filter_input(INPUT_POST, 'rental_id', FILTER_VALIDATE_INT);
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $reason = trim($_POST['reason'] ?? '');

    if ($rentalId && $customerId && $amount !== false && $amount >= 0 && $reason !== '') {
        $insertStmt = $conn->prepare('INSERT INTO fines (rental_id, customer_id, amount, reason) VALUES (?, ?, ?, ?)');
        $insertStmt->bind_param('iids', $rentalId, $customerId, $amount, $reason);
        if ($insertStmt->execute()) {
            $insertStmt->close();
            $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Fine applied successfully.'];
            header('Location: manage_fines.php');
            exit;
        }
        error_log('Failed to insert fine: ' . $insertStmt->error);
        $insertStmt->close();
        $flash = ['type' => 'error', 'text' => 'Unable to apply fine. Please try again.'];
    } else {
        $flash = ['type' => 'error', 'text' => 'Please provide a valid amount and reason.'];
    }
}

if (!$rentalId) {
    echo 'Error: Rental ID not provided.';
    exit;
}

$detailsStmt = $conn->prepare('
    SELECT u.name AS customer_name, r.user_id AS customer_id, r.date_from, r.date_to
    FROM rentals r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.rental_id = ?
');
$detailsStmt->bind_param('i', $rentalId);
$detailsStmt->execute();
$detailsResult = $detailsStmt->get_result();
$rentalDetails = $detailsResult->fetch_assoc();
$detailsStmt->close();

if (!$rentalDetails) {
    echo 'Error: Rental ID not found.';
    exit;
}

$customerId = (int) $rentalDetails['customer_id'];
$customerName = $rentalDetails['customer_name'];
$rentalPeriod = $rentalDetails['date_from'] . ' to ' . $rentalDetails['date_to'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Fine</title>
    <link rel="icon" type="image/png" href="../Assets/Images/DriveXpert.png">
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
        <?php if ($flash): ?>
            <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success'; ?>">
                <?= htmlspecialchars($flash['text']); ?>
            </div>
        <?php endif; ?>
        <div class="form-container">
            <form method="post" class="styled-form">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                <input type="hidden" name="rental_id" value="<?= $rentalId; ?>">
                <input type="hidden" name="customer_id" value="<?= $customerId; ?>">
                <!-- Customer Details -->
                <div class="form-group">
                    <label for="customer_name">Customer</label>
                    <input type="text" id="customer_name" value="<?= htmlspecialchars($customerName); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="rental_id">Rental ID</label>
                    <input type="text" id="rental_id" value="<?= $rentalId; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="rental_period">Rental Period</label>
                    <input type="text" id="rental_period" value="<?= htmlspecialchars($rentalPeriod); ?>" readonly>
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
<?php $conn->close(); ?>