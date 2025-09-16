<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-form.php");
    exit;
}

// Ensure agent_id is always available
if (!isset($_SESSION['agent_id']) && isset($_SESSION['user_id'])) {
    $_SESSION['agent_id'] = $_SESSION['user_id'];
}
?>
