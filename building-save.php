<?php
require 'auth/auth_check.php';
require 'config/db.php';

// ensure session agent id is available
$sessionAgentId = $_SESSION['agent_id'] ?? $_SESSION['user_id'] ?? null;
if (!$sessionAgentId) {
    header("Location: login-form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize and cast
    $postedAgentId = isset($_POST['agent_id']) ? (int) $_POST['agent_id'] : 0;

    // enforce that the posted agent id equals the logged-in agent id
    if ($postedAgentId !== (int)$sessionAgentId) {
        // security: ignore posted value and use session value (or reject)
        // Option A: reject:
        die("Invalid agent selection. You may only create buildings for your own account.");
        // Option B (safer UX): uncomment below to override instead of dying:
        // $postedAgentId = (int)$sessionAgentId;
    }

    $agent_id = $sessionAgentId;
    $landlord_id = isset($_POST['landlord_id']) ? (int) $_POST['landlord_id'] : null;
    $name = trim($_POST['name'] ?? '');
    $county = trim($_POST['county'] ?? '');

    // basic validation
    if (!$landlord_id || $name === '' || $county === '') {
        die("Please fill all required fields.");
    }

    $stmt = $conn->prepare("INSERT INTO buildings (agent_id, landlord_id, name, county) VALUES (?, ?, ?, ?)");
    $stmt->execute([$agent_id, $landlord_id, $name, $county]);

    header("Location: buildings.php?success=added");
    exit;
}
?>
