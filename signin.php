<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Update the query to also select the role
    $stmt = $conn->prepare("SELECT password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password, $role);
    $stmt->fetch();

    // Check if the user exists and verify the password
    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        // Redirect based on the user's role
        if ($role === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($role === 'customer') {
            header("Location: Client/Home.php");
        } else {
            echo "Unrecognized role.";
        }
        exit(); // Make sure to exit after redirection
    } else {
        echo "Invalid credentials.";
    }

    $stmt->close();
    $conn->close();
}
?>
