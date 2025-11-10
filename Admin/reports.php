<?php
include '../config.php';
include 'admin_guard.php';

// Get current date, month, and year
$currentDate = date('Y-m-d');
$currentMonth = date('Y-m');
$currentYear = date('Y');

// Daily Report
$dailyQuery = "SELECT COUNT(rental_id) AS total_rentals, SUM(total_cost) AS total_revenue 
               FROM rentals WHERE DATE(created_at) = '$currentDate'";
$dailyResult = $conn->query($dailyQuery)->fetch_assoc();

// Monthly Report
$monthlyQuery = "SELECT COUNT(rental_id) AS total_rentals, SUM(total_cost) AS total_revenue 
                 FROM rentals WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";
$monthlyResult = $conn->query($monthlyQuery)->fetch_assoc();

// Yearly Report
$yearlyQuery = "SELECT COUNT(rental_id) AS total_rentals, SUM(total_cost) AS total_revenue 
                FROM rentals WHERE YEAR(created_at) = '$currentYear'";
$yearlyResult = $conn->query($yearlyQuery)->fetch_assoc();

// Export function for CSV download
function exportCSV($data, $filename)
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');

    $output = fopen("php://output", "w");
    fputcsv($output, array_keys($data[0])); // Headers

    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

// Check if export is requested
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    if (!in_array($type, ['daily', 'monthly', 'yearly'], true)) {
        header('Location: reports.php');
        exit;
    }
    $filename = $type . "_report.csv";
    $data = [];

    if ($type === 'daily' && $dailyResult) {
        $data[] = $dailyResult;
    } elseif ($type === 'monthly' && $monthlyResult) {
        $data[] = $monthlyResult;
    } elseif ($type === 'yearly' && $yearlyResult) {
        $data[] = $yearlyResult;
    }

    if (!empty($data)) {
        exportCSV($data, $filename);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
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
        <h1>Reports</h1>

        <!-- Daily Report Section -->
        <div class="report-section">
            <h2>Daily Report (<?= htmlspecialchars($currentDate); ?>)</h2>
            <p><strong>Total Rentals:</strong> <?= htmlspecialchars($dailyResult['total_rentals'] ?? 0); ?></p>
            <p><strong>Total Revenue:</strong> $<?= number_format((float) ($dailyResult['total_revenue'] ?? 0), 2); ?>
            </p>
            <a href="?export=daily" class="btn download-btn">Download Daily Report</a>
        </div>

        <!-- Monthly Report Section -->
        <div class="report-section">
            <h2>Monthly Report (<?= htmlspecialchars(date('F Y')); ?>)</h2>
            <p><strong>Total Rentals:</strong> <?= htmlspecialchars($monthlyResult['total_rentals'] ?? 0); ?></p>
            <p><strong>Total Revenue:</strong> $<?= number_format((float) ($monthlyResult['total_revenue'] ?? 0), 2); ?>
            </p>
            <a href="?export=monthly" class="btn download-btn">Download Monthly Report</a>
        </div>

        <!-- Yearly Report Section -->
        <div class="report-section">
            <h2>Yearly Report (<?= htmlspecialchars($currentYear); ?>)</h2>
            <p><strong>Total Rentals:</strong> <?= htmlspecialchars($yearlyResult['total_rentals'] ?? 0); ?></p>
            <p><strong>Total Revenue:</strong> $<?= number_format((float) ($yearlyResult['total_revenue'] ?? 0), 2); ?>
            </p>
            <a href="?export=yearly" class="btn download-btn">Download Yearly Report</a>
        </div>
    </main>

</body>

</html>
<?php $conn->close(); ?>