<?php
require 'auth/auth_check.php';
require 'config/db.php';
require 'lib/ledger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: payments.php"); exit;
}

$agent_id = $_SESSION['agent_id'] ?? null;
$id = (int)($_POST['id'] ?? 0);
$ledger_id = (int)($_POST['ledger_id'] ?? 0);
$amount_paid = (float)($_POST['amount_paid'] ?? 0);
$payment_date = $_POST['payment_date'] ?? date('Y-m-d');

if ($id <= 0 || $ledger_id <= 0 || $amount_paid <= 0) {
    die("Invalid input");
}

// verify ownership
$stmt = $conn->prepare("SELECT id FROM payments WHERE id = ? AND agent_id = ?");
$stmt->execute([$id, $agent_id]);
if (!$stmt->fetch()) {
    die("Payment not found or unauthorized");
}

try {
    $conn->beginTransaction();

    $upd = $conn->prepare("UPDATE payments SET amount_paid=?, payment_date=? WHERE id=? AND agent_id=?");
    $upd->execute([$amount_paid, $payment_date, $id, $agent_id]);

    recalc_ledger_from_payments($conn, $ledger_id);

    $conn->commit();
    header("Location: payments.php?success=updated");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    die("Error updating payment: " . $e->getMessage());
}
