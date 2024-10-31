<?php
// functions.php
include '../config.php';

// Fetch Monthly Rentals for Bar Chart
function getMonthlyRentals($conn) {
    $sql = "SELECT MONTH(date_from) AS month, COUNT(rental_id) AS rentals 
            FROM rentals 
            WHERE YEAR(date_from) = YEAR(CURDATE()) 
            GROUP BY MONTH(date_from)";
    $result = $conn->query($sql);

    $monthlyRentals = [];
    while ($row = $result->fetch_assoc()) {
        $monthlyRentals[$row['month']] = $row['rentals'];
    }
    return $monthlyRentals;
}

// Fetch Revenue Distribution for Pie Chart
function getRevenueDistribution($conn) {
    $sql = "SELECT category, SUM(amount) AS revenue
            FROM (
                SELECT 'Cars' AS category, SUM(total_cost) AS amount FROM rentals WHERE status = 'completed'
                UNION ALL
                SELECT 'Accessories' AS category, SUM(extra_cost) FROM rental_accessories
                UNION ALL
                SELECT 'Insurance' AS category, SUM(insurance_cost) FROM rental_insurance
            ) AS revenue_summary
            GROUP BY category";
    $result = $conn->query($sql);

    $revenueDistribution = [];
    while ($row = $result->fetch_assoc()) {
        $revenueDistribution[$row['category']] = $row['revenue'];
    }
    return $revenueDistribution;
}


// Fetch Revenue Over Time for Line Chart
function getRevenueOverTime($conn) {
    $sql = "SELECT MONTH(date_from) AS month, SUM(total_cost) AS revenue 
            FROM rentals 
            WHERE YEAR(date_from) = YEAR(CURDATE()) 
            AND status = 'completed'
            GROUP BY MONTH(date_from)";
    $result = $conn->query($sql);

    $monthlyRevenue = [];
    while ($row = $result->fetch_assoc()) {
        $monthlyRevenue[$row['month']] = $row['revenue'];
    }
    return $monthlyRevenue;
}
?>
