<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Secure session start ---
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 1 day
        'cookie_secure'   => isset($_SERVER['HTTPS']), 
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM agents WHERE email = :email LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // 🛡️ Prevent session fixation
        session_regenerate_id(true);

        // ✅ Store all needed session variables
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['agent_id']  = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role_type'] = "Agent";

        // Debugging (comment this out later)
        // echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit;

        header("Location: ../dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login-form.php");
        exit;
    }
}
