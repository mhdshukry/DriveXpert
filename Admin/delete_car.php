<?php
include '../config.php';
include 'admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_cars.php');
    exit;
}

requireCsrfToken($_POST['csrf_token'] ?? null);

$car_id = $_POST['car_id'] ?? '';
if (!ctype_digit((string) $car_id)) {
    echo 'Invalid car id';
    exit;
}
$car_id = (int) $car_id;

$stmt = $conn->prepare('DELETE FROM cars WHERE car_id = ?');
if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    echo 'Internal error';
    exit;
}
$stmt->bind_param('i', $car_id);
if ($stmt->execute()) {
    header('Location: manage_cars.php');
    exit;
} else {
    error_log('Delete failed: ' . $stmt->error);
    echo 'Unable to delete car.';
}
$stmt->close();
$conn->close();
?>