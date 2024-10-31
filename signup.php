<?php
// Database connection
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $nic_number = $_POST['nic_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Prepare SQL insert query
    $query = "INSERT INTO users (name, email, phone, nic_number, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $name, $email, $phone, $nic_number, $password);

    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: auth.php?action=signin"); // Redirect to sign-in
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
