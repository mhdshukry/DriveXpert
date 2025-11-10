<?php
// Admin access guard: include this at the top of every admin page after session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth.php');
    exit;
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $fallback = openssl_random_pseudo_bytes(32);
        if ($fallback !== false) {
            $_SESSION['csrf_token'] = bin2hex($fallback);
        } else {
            $hash = hash('sha256', microtime(true) . session_id() . mt_rand(), true);
            $_SESSION['csrf_token'] = bin2hex(substr($hash, 0, 16));
        }
    }
}

if (!function_exists('requireCsrfToken')) {
    function requireCsrfToken(?string $token): void
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(400);
            exit('Invalid request token.');
        }
    }
}
?>