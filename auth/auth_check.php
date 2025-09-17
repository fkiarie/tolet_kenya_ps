<?php
// auth_check.php

// --- Force session start safely ---
if (session_status() === PHP_SESSION_NONE) {
    // Explicitly set secure session options
    session_start([
        'cookie_lifetime' => 86400, // 1 day
        'cookie_secure'   => isset($_SERVER['HTTPS']), // secure only if HTTPS
        'cookie_httponly' => true,   // protect against JS access
        'cookie_samesite' => 'Lax',  // avoid CSRF issues while allowing normal navigation
    ]);
}

// --- Check if user is logged in ---
if (empty($_SESSION['user_id']) || empty($_SESSION['agent_id'])) {
    // No active login
    header("Location: login-form.php");
    exit;
}

// --- OPTIONAL: regenerate session every X requests for safety ---
if (!isset($_SESSION['regenerated'])) {
    $_SESSION['regenerated'] = time();
} elseif (time() - $_SESSION['regenerated'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}

// --- OPTIONAL: Restrict roles ---
$allowedRoles = ['Agent'];
if (!in_array($_SESSION['role_type'], $allowedRoles)) {
    die("Access Denied: You do not have permission to access this page.");
}

// Debugging (comment out in production)
// echo "Session ID: " . session_id();
// echo "<pre>"; print_r($_SESSION); echo "</pre>";
?>
