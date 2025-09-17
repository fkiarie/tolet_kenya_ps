<?php
require 'auth/auth_check.php';
require 'config/db.php';
require 'lib/ledger.php';

$agent_id = $_SESSION['agent_id'] ?? null;
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Payment not specified.");
}

$stmt = $conn->prepare("SELECT ledger_id FROM payments WHERE id=? AND agent_id=?");
$stmt->execute([$id, $agent_id]);
$ledger_id = $stmt->fetchColumn();

if (!$ledger_id) {
    die("Payment not found or unauthorized.");
}

try {
    $conn->beginTransaction();

    $del = $conn->prepare("DELETE FROM payments WHERE id=? AND agent_id=?");
    $del->execute([$id, $agent_id]);

    recalc_ledger_from_payments($conn, $ledger_id);

    $conn->commit();
    header("Location: payments.php?success=deleted");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    die("Error deleting payment: " . $e->getMessage());
}
