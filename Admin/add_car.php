<?php
include '../config.php'; // Include the database configuration
include 'admin_guard.php';

function validateUploadedImage($file, $maxSizeBytes = 2_000_000)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'File upload error.';
    }
    if ($file['size'] > $maxSizeBytes) {
        return 'File too large (max 2MB).';
    }
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return 'Invalid file extension.';
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowedMime, true)) {
        return 'Invalid image MIME type.';
    }
    return true;
}

function uniqueImageName($original)
{
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(8)) . '.' . $ext;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken($_POST['csrf_token'] ?? null);
    // Retrieve and sanitize form data
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $seat_count = (int) $_POST['seat_count'];
    $max_speed = (int) $_POST['max_speed'];
    $km_per_liter = (float) $_POST['km_per_liter'];
    $rent_per_day = (float) $_POST['rent_per_day'];
    $availability = isset($_POST['availability']) ? 1 : 0;

    // Validate uploads
    $logoValidation = validateUploadedImage($_FILES['logo_picture']);
    $carValidation = validateUploadedImage($_FILES['car_picture']);
    if ($logoValidation !== true || $carValidation !== true) {
        $msg = $logoValidation === true ? $carValidation : $logoValidation;
        echo "<script>alert('" . htmlspecialchars($msg, ENT_QUOTES) . "');</script>";
    } else {
        $target_dir = '../Assets/Images/uploads/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $logo_filename = uniqueImageName($_FILES['logo_picture']['name']);
        $car_filename = uniqueImageName($_FILES['car_picture']['name']);
        $logo_path = 'Assets/Images/uploads/' . $logo_filename;
        $car_path = 'Assets/Images/uploads/' . $car_filename;

        if (
            !move_uploaded_file($_FILES['logo_picture']['tmp_name'], $target_dir . $logo_filename) ||
            !move_uploaded_file($_FILES['car_picture']['tmp_name'], $target_dir . $car_filename)
        ) {
            echo "<script>alert('Failed to move uploaded files.');</script>";
        } else {
            $stmt = $conn->prepare('INSERT INTO cars (brand, model, seat_count, max_speed, km_per_liter, rent_per_day, availability, logo_picture, car_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            if (!$stmt) {
                error_log('Prepare failed: ' . $conn->error);
                echo "<script>alert('Internal error.');</script>";
            } else {
                // Keep original binding pattern to avoid unintended type mismatches
                $stmt->bind_param('ssiiidiss', $brand, $model, $seat_count, $max_speed, $km_per_liter, $rent_per_day, $availability, $logo_path, $car_path);
                if ($stmt->execute()) {
                    echo "<script>alert('Car added successfully!'); window.location.href='manage_cars.php';</script>";
                } else {
                    error_log('Insert car failed: ' . $stmt->error);
                    echo "<script>alert('Error adding car. Please try again.');</script>";
                }
                $stmt->close();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car</title>
    <link rel="icon" type="image/png" href="../Assets/Images/DriveXpert.png">
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
    <link rel="stylesheet" href="../Assets/CSS/add_car.css">
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
        <h1>Add New Car</h1>
        <div class="form-container">
            <form action="" method="post" enctype="multipart/form-data" class="add-car-form">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                <div class="form-group">
                    <label for="brand">Car Brand</label>
                    <input type="text" id="brand" name="brand" required>
                </div>
                <div class="form-group">
                    <label for="model">Car Model</label>
                    <input type="text" id="model" name="model" required>
                </div>
                <div class="form-group">
                    <label for="seat_count">Seat Count</label>
                    <input type="number" id="seat_count" name="seat_count" min="1" required>
                </div>
                <div class="form-group">
                    <label for="max_speed">Max Speed (km/h)</label>
                    <input type="number" id="max_speed" name="max_speed" required>
                </div>
                <div class="form-group">
                    <label for="km_per_liter">Km per Liter</label>
                    <input type="text" id="km_per_liter" name="km_per_liter" required>
                </div>
                <div class="form-group">
                    <label for="rent_per_day">Rent per Day ($)</label>
                    <input type="text" id="rent_per_day" name="rent_per_day" required>
                </div>
                <div class="form-group">
                    <label for="availability">Availability</label>
                    <input type="checkbox" id="availability" name="availability" checked> Available
                </div>
                <div class="form-group">
                    <label for="logo_picture">Logo Picture (Max 2MB)</label>
                    <input type="file" id="logo_picture" name="logo_picture"
                        accept="image/png,image/jpeg,image/webp,image/gif" required>
                </div>
                <div class="form-group">
                    <label for="car_picture">Car Picture (Max 2MB)</label>
                    <input type="file" id="car_picture" name="car_picture"
                        accept="image/png,image/jpeg,image/webp,image/gif" required>
                </div>
                <button type="submit" class="btn submit-btn">Add Car</button>
            </form>
        </div>
    </main>

</body>

</html>
<?php $conn->close(); ?>