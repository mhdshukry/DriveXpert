<?php
include '../config.php';
include './functions.php';

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
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
    </div>
    <nav class="nav-links">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_rentals.php">Rentals</a>
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
        <!-- Placeholder metrics, replace these values with dynamic PHP if needed -->
        <div class="card"><h3>Total Rentals</h3><p>125</p></div>
        <div class="card"><h3>Revenue</h3><p>$12,500</p></div>
        <div class="card"><h3>Active Customers</h3><p>320</p></div>
    </div>

    <!-- Charts Section -->
    <div class="chart-grid">
        <div class="chart-container">
            <h3>Monthly Rentals</h3>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Revenue Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="chart-container full-width">
            <h3>Revenue Over Time</h3>
            <canvas id="lineChart"></canvas>
        </div>
    </div>
</main>

<footer class="footer-bottom">
    <p>&copy; 2024 DriveXpert Admin Dashboard.</p>
</footer>

<script>
// Monthly Rentals Bar Chart
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Rentals',
            data: <?php echo json_encode($monthlyRentalsData); ?>,
            backgroundColor: '#db1111',
            borderRadius: 5,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

// Revenue Distribution Pie Chart
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: <?php echo $revenueLabels; ?>,
        datasets: [{
            data: <?php echo $revenueData; ?>,
            backgroundColor: ['#db1111', '#ff5733', '#ffc300', '#28a745'],
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Revenue Over Time Line Chart
const lineCtx = document.getElementById('lineChart').getContext('2d');
new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($monthlyRevenueData); ?>,
            borderColor: '#db1111',
            fill: false,
            tension: 0.4
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

</body>
</html>
<?php $conn->close(); ?>
