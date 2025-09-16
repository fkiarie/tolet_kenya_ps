<?php
require 'auth/auth_check.php';
require 'config/db.php';

// ensure session agent id is available
$sessionAgentId = $_SESSION['agent_id'] ?? $_SESSION['user_id'] ?? null;
echo $sessionAgentId;
if (!$sessionAgentId) {
    header("Location: login-form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize and cast
    $postedAgentId = isset($_POST['agent_id']) ? (int) $_POST['agent_id'] : 0;

    // enforce that the posted agent id equals the logged-in agent id
    if ($postedAgentId !== (int)$sessionAgentId) {
        $_SESSION['error'] = "Invalid agent selection. You may only create buildings for your own account.";
        header("Location: add-building.php");
        exit;
    }

    $agent_id = $sessionAgentId;
    $landlord_id = isset($_POST['landlord_id']) ? (int) $_POST['landlord_id'] : null;
    $name = trim($_POST['name'] ?? '');
    $county = trim($_POST['county'] ?? '');

    // basic validation
    if (!$landlord_id || $name === '' || $county === '') {
        $_SESSION['error'] = "Please fill all required fields.";
        header("Location: building-add.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO buildings (agent_id, landlord_id, name, county) VALUES (?, ?, ?, ?)");
    $stmt->execute([$agent_id, $landlord_id, $name, $county]);

    $_SESSION['success'] = "Building added successfully!";
    header("Location: buildings.php");
    exit;
}
