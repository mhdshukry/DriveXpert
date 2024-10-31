<?php
include '../config.php';

if (isset($_GET['id'])) {
    $car_id = $_GET['id'];
    $sql = "DELETE FROM cars WHERE car_id = $car_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: manage_cars.php");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>
