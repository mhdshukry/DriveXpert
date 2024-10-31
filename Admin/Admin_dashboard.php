<?php
include '../config.php';
include './functions.php';

// Fetch actual data for the dashboard
$totalRentalsQuery = "SELECT COUNT(*) as total_rentals FROM rentals";
$totalRentalsResult = $conn->query($totalRentalsQuery);
$totalRentals = $totalRentalsResult->fetch_assoc()['total_rentals'];

$totalRevenueQuery = "SELECT SUM(total_cost) as total_revenue FROM rentals WHERE status = 'completed'";
$totalRevenueResult = $conn->query($totalRevenueQuery);
$totalRevenue = $totalRevenueResult->fetch_assoc()['total_revenue'] ?? 0; // Default to 0 if no revenue

// Updated query to count active customers (assuming no status column)
$activeCustomersQuery = "SELECT COUNT(*) as active_customers FROM users WHERE role = 'customer'";
$activeCustomersResult = $conn->query($activeCustomersQuery);
$activeCustomers = $activeCustomersResult->fetch_assoc()['active_customers'];

// Fetch data for the charts
$monthlyRentals = getMonthlyRentals($conn);
$revenueDistribution = getRevenueDistribution($conn);
$monthlyRevenue = getRevenueOverTime($conn);

// Generate JavaScript-compatible data arrays
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthlyRentalsData = array_fill(0, 12, 0);
foreach ($monthlyRentals as $month => $count) {
    $monthlyRentalsData[$month - 1] = $count;
}
$revenueLabels = json_encode(array_keys($revenueDistribution));
$revenueData = json_encode(array_values($revenueDistribution));
$monthlyRevenueData = array_fill(0, 12, 0);
foreach ($monthlyRevenue as $month => $revenue) {
    $monthlyRevenueData[$month - 1] = $revenue;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* General Styling */
        .card, .car-card, .rental-details-card, .new-request-card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 10px;
            background-color: #ffffff;
            transition: transform 0.2s ease-in-out;
        }
        .card:hover, .car-card:hover, .rental-details-card:hover, .new-request-card:hover {
            transform: scale(1.02);
        }
        .section-heading {
            font-size: 1.8em;
            margin-bottom: 20px;
            color: #db1111;
        }
        .card-container, .chart-grid, .car-gallery, .rental-details-section, .new-requests-section {
            display: grid;
            gap: 20px;
        }
        .card-container {
            grid-template-columns: repeat(3, 1fr);
        }
        .chart-grid {
            grid-template-columns: 1fr 1fr;
        }
        .car-gallery, .rental-details-section {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
        .new-requests-section {
            grid-template-columns: 1fr;
        }
        .car-card img {
            width: 100%;
            border-radius: 8px;
        }
        .car-card-details, .rental-details, .new-request-details {
            font-size: 0.9em;
            color: #444;
        }
        .btn1 {
            background-color: #db1111;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn1:hover {
            background-color: #a50d0d;
        }
    </style>
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
    <h1>Dashboard Overview</h1>

    <!-- Key Metrics Cards -->
    <div class="card-container">
        <div class="card"><h3>Total Rentals</h3><p><?php echo $totalRentals; ?></p></div>
        <div class="card"><h3>Revenue</h3><p>$<?php echo number_format($totalRevenue, 2); ?></p></div>
        <div class="card"><h3>Active Customers</h3><p><?php echo $activeCustomers; ?></p></div>
    </div>

    <section>
    <h2 class="section-heading">Available Cars</h2>
    <div class="car-gallery">
        <?php
        $carQuery = "SELECT model, brand, availability, car_picture FROM cars LIMIT 6"; // Fetch a few car details
        $carResult = $conn->query($carQuery);
        while ($car = $carResult->fetch_assoc()) {
            echo "
            <div class='car-card'>
                <img src='../{$car['car_picture']}' alt='{$car['model']}'>
                <div class='car-card-details'>
                    <h4>{$car['brand']} {$car['model']}</h4>
                    <p>Availability: " . ($car['availability'] ? "Available" : "Rented") . "</p>
                    <button class='btn1'>View Details</button>
                </div>
            </div>";
        }
        ?>
    </div>
</section>


    <!-- Rental Details Section -->
    <section>
        <h2 class="section-heading">Rental Details and Due Dates</h2>
        <div class="rental-details-section">
            <?php
            $rentalQuery = "SELECT r.rental_id, u.name AS customer_name, r.date_to, c.model AS car_model 
                            FROM rentals r 
                            JOIN users u ON r.user_id = u.user_id 
                            JOIN cars c ON r.car_id = c.car_id 
                            WHERE r.status = 'confirmed'
                            ORDER BY r.date_to ASC LIMIT 4";
            $rentalResult = $conn->query($rentalQuery);
            while ($rental = $rentalResult->fetch_assoc()) {
                echo "
                <div class='rental-details-card'>
                    <div class='rental-details'>
                        <h4>Rental ID: {$rental['rental_id']}</h4>
                        <p>Customer: {$rental['customer_name']}</p>
                        <p>Car Model: {$rental['car_model']}</p>
                        <p>Due Date: {$rental['date_to']}</p>
                        <button class='btn1'>Manage Rental</button>
                    </div>
                </div>";
            }
            ?>
        </div>
    </section>

    <!-- New Rental Requests Section -->
    <section>
        <h2 class="section-heading">New Rental Requests</h2>
        <div class="new-requests-section">
            <?php
            $newRequestQuery = "SELECT r.rental_id, u.name AS customer_name, c.model AS car_model, r.date_from 
                                FROM rentals r 
                                JOIN users u ON r.user_id = u.user_id 
                                JOIN cars c ON r.car_id = c.car_id 
                                WHERE r.status = 'pending'
                                ORDER BY r.date_from DESC LIMIT 4";
            $newRequestResult = $conn->query($newRequestQuery);
            while ($request = $newRequestResult->fetch_assoc()) {
                echo "
                <div class='new-request-card'>
                    <div class='new-request-details'>
                        <h4>Rental ID: {$request['rental_id']}</h4>
                        <p>Customer: {$request['customer_name']}</p>
                        <p>Requested Car: {$request['car_model']}</p>
                        <p>Start Date: {$request['date_from']}</p>
                        <button class='btn1'>Approve Request</button>
                    </div>
                </div>";
            }
            ?>
        </div>
    </section>

    <div class="chart-grid">
        <div class="chart-container">
            <h3>Monthly Rentals</h3>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Revenue Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Revenue Over Time</h3>
            <canvas id="lineChart"></canvas>
        </div>
    </div>

</main>

<footer class="footer-bottom">
    <p>&copy; 2024 DriveXpert Admin Dashboard.</p>
</footer>

<script>
// Customizable Chart Colors with Gradient
const createGradient = (ctx, color1, color2) => {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
};

// Monthly Rentals Bar Chart with Gradient and Shadow
const barCtx = document.getElementById('barChart').getContext('2d');
const barGradient = createGradient(barCtx, '#ff7f50', '#db1111');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Rentals',
            data: <?php echo json_encode($monthlyRentalsData); ?>,
            backgroundColor: barGradient,
            borderColor: '#db1111',
            borderWidth: 1,
            borderRadius: 5,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#fff',
                titleColor: '#333',
                titleFont: { size: 13, weight: 'bold' },
                bodyFont: { size: 12 },
                bodyColor: '#666',
                boxPadding: 6
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#333', font: { size: 13 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#eee', lineWidth: 1 },
                ticks: { color: '#666', font: { size: 12 } }
            }
        }
    }
});

// Revenue Distribution Pie Chart with Sleek Colors
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo $revenueLabels; ?>,
        datasets: [{
            data: <?php echo $revenueData; ?>,
            backgroundColor: ['#ff7f50', '#db1111', '#ffd700', '#32cd32'],
            hoverBackgroundColor: ['#ff6347', '#c21807', '#ffa500', '#228b22'],
            borderColor: '#fff',
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%', // Makes the doughnut chart more compact
        plugins: {
            legend: {
                position: 'right',
                labels: { font: { size: 13, family: 'Arial' }, color: '#555' }
            },
            tooltip: {
                backgroundColor: '#fff',
                titleColor: '#333',
                bodyColor: '#666',
                titleFont: { size: 13, weight: 'bold' },
                boxPadding: 5
            }
        }
    }
});

// Revenue Over Time Line Chart with Smooth Curves and Shadow
const lineCtx = document.getElementById('lineChart').getContext('2d');
const lineGradient = createGradient(lineCtx, 'rgba(219, 17, 17, 0.4)', 'rgba(255, 127, 80, 0)');
new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($monthlyRevenueData); ?>,
            borderColor: '#db1111',
            backgroundColor: lineGradient,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#ff7f50',
            pointRadius: 5,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: '#db1111',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#fff',
                titleColor: '#333',
                bodyColor: '#666',
                titleFont: { size: 13, weight: 'bold' },
                boxPadding: 5
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#333', font: { size: 12 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#eee', lineWidth: 1 },
                ticks: { color: '#666', font: { size: 12 } }
            }
        }
    }
});
</script>

</body>
</html>
<?php $conn->close(); ?>
