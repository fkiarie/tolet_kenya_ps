<?php
// auth_check.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login-form.php");
    exit;
}

// OPTIONAL: restrict roles
// $allowedRoles = ['Agent'];
// if (!in_array($_SESSION['role_type'], $allowedRoles)) {
//     die("Access Denied: You do not have permission to access this page.");
// }
?>
