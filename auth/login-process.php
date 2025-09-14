<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM agents WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];   // keep this
        $_SESSION['agent_id']  = $user['id'];   // 👈 add this
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role_type'] = "Agent";

        header("Location: ../dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login-form.php");
        exit;
    }
}
