<?php
include '../config.php';
include 'admin_guard.php';

// Fetch car details securely
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $car_id = (int) $_GET['id'];
    $stmt = $conn->prepare('SELECT car_id, brand, model, seat_count, max_speed, km_per_liter, rent_per_day, availability FROM cars WHERE car_id = ?');
    $stmt->bind_param('i', $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
    $stmt->close();
    if (!$car) {
        echo 'Car not found.';
        exit;
    }
} else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Invalid car id.';
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken($_POST['csrf_token'] ?? null);
    $car_id = $_POST['car_id'];
    if (!ctype_digit($car_id)) {
        echo 'Invalid car id.';
        exit;
    }
    $car_id = (int) $car_id;
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $rent_per_day = isset($_POST['rent_per_day']) ? (float) $_POST['rent_per_day'] : 0;
    $availability = isset($_POST['availability']) ? 1 : 0;

    $stmt = $conn->prepare('UPDATE cars SET brand = ?, model = ?, rent_per_day = ?, availability = ? WHERE car_id = ?');
    $stmt->bind_param('ssdii', $brand, $model, $rent_per_day, $availability, $car_id);
    if ($stmt->execute()) {
        header('Location: manage_cars.php');
        exit;
    } else {
        error_log('Update car failed: ' . $stmt->error);
        echo 'Unable to update car.';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>
    <link rel="icon" type="image/png" href="../Assets/Images/DriveXpert.png">
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
</head>

<body>
    <header class="header">
        <div class="logo">
            <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
        </div>
        <nav class="nav-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <div class="dropdown">
                <a class="dropdown-toggle">Rentals</a>
                <div class="dropdown-menu">
                    <a href="confirm_rental.php">Confirm Rental</a>
                    <a href="rental_history.php">Rental History</a>
                    <a href="manage_rentals.php">Manage Rental</a>
                </div>
            </div>
            <a href="manage_customers.php">Customers</a>
            <a href="manage_cars.php" class="active">Cars</a>
            <a href="manage_fines.php">Fines</a>
            <a href="reports.php">Reports</a>
        </nav>
        <div class="auth-buttons">
            <button class="btn logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </header>
    <main class="main-content">
        <h1>Edit Car Details</h1>
        <form action="" method="post" class="form-container">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
            <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['car_id']) ?>">

            <label for="brand">Brand:</label>
            <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($car['brand']) ?>" required>

            <label for="model">Model:</label>
            <input type="text" id="model" name="model" value="<?= htmlspecialchars($car['model']) ?>" required>

            <label for="rent_per_day">Rent Per Day ($):</label>
            <input type="number" step="0.01" id="rent_per_day" name="rent_per_day"
                value="<?= htmlspecialchars($car['rent_per_day']) ?>" required>

            <label for="availability">Availability:</label>
            <input type="checkbox" id="availability" name="availability" <?= $car['availability'] ? 'checked' : '' ?>>

            <button type="submit" class="btn">Update Car</button>
        </form>
    </main>
</body>

</html>
<?php $conn->close(); ?>