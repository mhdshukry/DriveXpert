<?php
session_start(); // Start the session

// Unset all of the session variables
$_SESSION = [];

// If it's desired to kill the session, also remove the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"], $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to the login page or homepage
header("Location: ../index.php");
exit();
?>
