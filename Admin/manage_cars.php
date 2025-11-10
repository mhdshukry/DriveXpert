<?php
include '../config.php';
include 'admin_guard.php';

// Fetch cars data securely
$result = $conn->query("SELECT car_id, brand, model, seat_count, max_speed, km_per_liter, rent_per_day, logo_picture, car_picture FROM cars");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Admin Dashboard</title>
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
                            <td><?= htmlspecialchars($row['car_id']) ?></td>
                            <td><?= htmlspecialchars($row['brand']) ?></td>
                            <td><?= htmlspecialchars($row['model']) ?></td>
                            <td><?= htmlspecialchars($row['seat_count']) ?></td>
                            <td><?= htmlspecialchars($row['max_speed']) ?> km/h</td>
                            <td><?= htmlspecialchars($row['km_per_liter']) ?> km/l</td>
                            <td>$<?= htmlspecialchars($row['rent_per_day']) ?></td>
                            <td><img src="../<?= htmlspecialchars($row['logo_picture']) ?>"
                                    alt="<?= htmlspecialchars($row['brand']) ?> logo"
                                    style="width:50px;height:auto;object-fit:contain;"></td>
                            <td><img src="../<?= htmlspecialchars($row['car_picture']) ?>"
                                    alt="<?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?>"
                                    style="width:120px;height:80px;object-fit:cover;border-radius:4px;"></td>
                            <td>
                                <a class="action-btn" href="edit_car.php?id=<?= (int) $row['car_id'] ?>">Edit</a>
                                <form method="post" action="delete_car.php" class="inline-form">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                                    <input type="hidden" name="car_id" value="<?= (int) $row['car_id'] ?>">
                                    <button type="submit" class="action-btn delete"
                                        onclick="return confirm('Are you sure you want to delete this car?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>
<?php $conn->close(); ?>