<?php
session_start(); // Start the session to store user data
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Update the query to also select the user_id and role
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $role);
    $stmt->fetch();

    // Check if the user exists and verify the password
    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        // Store user_id and role in session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;

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
