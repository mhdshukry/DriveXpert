<?php
// Database connection
include 'config.php';

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
        $return = isset($_POST['return']) ? $_POST['return'] : '';
        $redir = 'auth.php?action=signin' . ($return ? ('&return=' . urlencode($return)) : '');
        header("Location: $redir"); // Redirect to sign-in with optional return URL
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>