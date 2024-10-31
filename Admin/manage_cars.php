<?php
include '../config.php';

// Fetch cars data
$query = "SELECT car_id, brand, model, seat_count, max_speed, km_per_liter, rent_per_day, logo_picture, car_picture FROM cars";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Admin Dashboard</title>
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
    <h1>Manage Cars</h1>
    <div class="add-car-button">
        <button class="btn1" onclick="window.location.href='add_car.php'">Add New Car</button>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Car ID</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Seats</th>
                    <th>Max Speed</th>
                    <th>Km/L</th>
                    <th>Rent/Day</th>
                    <th>Logo</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['car_id'] ?></td>
                        <td><?= $row['brand'] ?></td>
                        <td><?= $row['model'] ?></td>
                        <td><?= $row['seat_count'] ?></td>
                        <td><?= $row['max_speed'] ?> km/h</td>
                        <td><?= $row['km_per_liter'] ?> km/l</td>
                        <td>$<?= $row['rent_per_day'] ?></td>
                        <td><img src="../<?= $row['logo_picture'] ?>" alt="Logo" style="width: 50px; height: auto;"></td>
                        <td><img src="../<?= $row['car_picture'] ?>" alt="Car Image" style="width: 100px; height: auto;"></td>
                        <td>
                            <button class="action-btn" onclick="editCar(<?= $row['car_id'] ?>)">Edit</button>
                            <button class="action-btn" onclick="deleteCar(<?= $row['car_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function editCar(id) {
    window.location.href = `edit_car.php?id=${id}`;
}

function deleteCar(id) {
    if (confirm("Are you sure you want to delete this car?")) {
        window.location.href = `delete_car.php?id=${id}`;
    }
}
</script>

</body>
</html>
