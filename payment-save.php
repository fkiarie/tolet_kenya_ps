<?php
require 'auth/auth_check.php';
require 'config/db.php';
require 'lib/ledger.php'; // contains recalc_ledger_from_payments()

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: payments.php"); exit;
}

$ledger_id = (int)($_POST['ledger_id'] ?? 0);
$amount_paid = (float)($_POST['amount_paid'] ?? 0);
$payment_date = $_POST['payment_date'] ?? date('Y-m-d');

if (!$ledger_id || $amount_paid <= 0) {
    die("Invalid input");
}

// Fetch ledger
$stmt = $conn->prepare("SELECT unit_id, tenant_id FROM rent_ledger WHERE id = ?");
$stmt->execute([$ledger_id]);
$ledger = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ledger) die("Ledger not found");

$unit_id = (int)$ledger['unit_id'];
$tenant_id = (int)$ledger['tenant_id'];

// Find landlord
$stmt2 = $conn->prepare("
    SELECT b.landlord_id 
    FROM units u 
    JOIN buildings b ON u.building_id = b.id 
    WHERE u.id = ?
");
$stmt2->execute([$unit_id]);
$landlord_id = $stmt2->fetchColumn();
if (!$landlord_id) die("Landlord not found");

// Current agent
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) die("Agent not in session");

try {
    $conn->beginTransaction();

    // Insert payment
    $ins = $conn->prepare("
        INSERT INTO payments 
        (ledger_id, unit_id, tenant_id, landlord_id, agent_id, amount_paid, payment_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([$ledger_id, $unit_id, $tenant_id, $landlord_id, $agent_id, $amount_paid, $payment_date]);

    // Recalculate ledger
    recalc_ledger_from_payments($conn, $ledger_id);

    $conn->commit();
    header("Location: payments.php?success=added");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    die("Error saving payment: " . $e->getMessage());
}
