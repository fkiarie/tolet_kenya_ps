<?php
require 'auth/auth_check.php';
require 'config/db.php';

$sessionAgentId = $_SESSION['agent_id'] ?? $_SESSION['user_id'] ?? null;
if (!$sessionAgentId) {
    header("Location: login-form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $postedAgentId = isset($_POST['agent_id']) ? (int) $_POST['agent_id'] : 0;

    // verify building exists and belongs to session agent
    $stmt = $conn->prepare("SELECT * FROM buildings WHERE id = ?");
    $stmt->execute([$id]);
    $building = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$building) die("Building not found.");
    if ((int)$building['agent_id'] !== (int)$sessionAgentId) {
        die("Access denied. You can only update your own buildings.");
    }

    // ensure posted agent matches session agent
    if ($postedAgentId !== (int)$sessionAgentId) {
        die("Invalid agent selection.");
    }

    // update fields
    $name = trim($_POST['name'] ?? '');
    $county = trim($_POST['county'] ?? '');
    $landlord_id = isset($_POST['landlord_id']) ? (int) $_POST['landlord_id'] : null;

    if ($name === '' || $county === '' || !$landlord_id) {
        die("Please fill all required fields.");
    }

    $update = $conn->prepare("UPDATE buildings SET name = ?, county = ?, landlord_id = ? WHERE id = ?");
    $update->execute([$name, $county, $landlord_id, $id]);

    header("Location: buildings.php?success=updated");
    exit;
}
?>
