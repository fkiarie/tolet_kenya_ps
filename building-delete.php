<?php
require 'auth/auth_check.php';
require 'config/db.php';

$sessionAgentId = $_SESSION['agent_id'] ?? $_SESSION['user_id'] ?? null;
if (!$sessionAgentId) {
    header("Location: login-form.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$id) die("Missing ID.");

// check building owner
$stmt = $conn->prepare("SELECT agent_id FROM buildings WHERE id = ?");
$stmt->execute([$id]);
$building = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$building) die("Building not found.");
if ((int)$building['agent_id'] !== (int)$sessionAgentId) {
    die("Access denied. You can only delete your own buildings.");
}

// safe to delete
$del = $conn->prepare("DELETE FROM buildings WHERE id = ?");
$del->execute([$id]);

header("Location: buildings.php?success=deleted");
exit;
?>
