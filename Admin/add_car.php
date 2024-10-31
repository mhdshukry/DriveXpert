<?php
include '../config.php'; // Include the database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $seat_count = $_POST['seat_count'];
    $max_speed = $_POST['max_speed'];
    $km_per_liter = $_POST['km_per_liter'];
    $rent_per_day = $_POST['rent_per_day'];
    $availability = isset($_POST['availability']) ? 1 : 0;
    
    // Handle logo and car pictures
    $logo_picture = $_FILES['logo_picture']['name'];
    $car_picture = $_FILES['car_picture']['name'];

    // Move uploaded images to a dedicated directory (e.g., "uploads")
    $target_dir = "../Assets/Images/uploads/";
    move_uploaded_file($_FILES['logo_picture']['tmp_name'], $target_dir . $logo_picture);
    move_uploaded_file($_FILES['car_picture']['tmp_name'], $target_dir . $car_picture);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO cars (brand, model, seat_count, max_speed, km_per_liter, rent_per_day, availability, logo_picture, car_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiidiss", $brand, $model, $seat_count, $max_speed, $km_per_liter, $rent_per_day, $availability, $logo_picture, $car_picture);

    if ($stmt->execute()) {
        echo "<script>alert('Car added successfully!'); window.location.href='manage_cars.php';</script>";
    } else {
        echo "<script>alert('Error adding car. Please try again.');</script>";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car</title>
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
                <label for="logo_picture">Logo Picture</label>
                <input type="file" id="logo_picture" name="logo_picture" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="car_picture">Car Picture</label>
                <input type="file" id="car_picture" name="car_picture" accept="image/*" required>
            </div>
            <button type="submit" class="btn submit-btn">Add Car</button>
        </form>
    </div>
</main>

</body>
</html>
