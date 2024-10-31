<?php
include '../config.php';

if (isset($_GET['id'])) {
    $car_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM cars WHERE car_id = $car_id");
    $car = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $car_id = $_POST['car_id'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $daily_rate = $_POST['daily_rate'];
    $availability = isset($_POST['availability']) ? 1 : 0;

    $sql = "UPDATE cars SET brand='$brand', model='$model', daily_rate=$daily_rate, availability=$availability WHERE car_id=$car_id";
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_cars.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>
    <link rel="stylesheet" href="../Assets/CSS/admin.css">
</head>
<body>
    <header class="header">
        <h1>Edit Car Details</h1>
    </header>
    <form action="" method="post">
        <input type="hidden" name="car_id" value="<?= $car['car_id'] ?>">
        
        <label for="brand">Brand:</label>
        <input type="text" id="brand" name="brand" value="<?= $car['brand'] ?>" required>
        
        <label for="model">Model:</label>
        <input type="text" id="model" name="model" value="<?= $car['model'] ?>" required>
        
        <label for="daily_rate">Daily Rate:</label>
        <input type="number" step="0.01" id="daily_rate" name="daily_rate" value="<?= $car['daily_rate'] ?>" required>
        
        <label for="availability">Availability:</label>
        <input type="checkbox" id="availability" name="availability" <?= $car['availability'] ? 'checked' : '' ?>>

        <button type="submit" class="btn">Update Car</button>
    </form>
</body>
</html>
