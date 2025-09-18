<?php
function recalc_ledger_from_payments(PDO $conn, int $ledger_id) {
    // Get total payments
    $stmt = $conn->prepare("SELECT SUM(amount_paid) FROM payments WHERE ledger_id = ?");
    $stmt->execute([$ledger_id]);
    $total_paid = (float)$stmt->fetchColumn();

    // Get rent due
    $stmt2 = $conn->prepare("SELECT rent_due FROM rent_ledger WHERE id = ?");
    $stmt2->execute([$ledger_id]);
    $rent_due = (float)$stmt2->fetchColumn();

    $balance = $rent_due - $total_paid;
    $status = "unpaid";
    if ($total_paid >= $rent_due) $status = "paid";
    elseif ($total_paid > 0 && $balance > 0) $status = "partial";

    // Update ledger
    $upd = $conn->prepare("UPDATE rent_ledger SET amount_paid=?, balance=?, status=? WHERE id=?");
    $upd->execute([$total_paid, $balance, $status, $ledger_id]);
}

function create_or_update_ledger_entry(PDO $conn, int $unit_id, int $tenant_id, int $year, int $month, float $rent_due) {
    // Check if ledger entry already exists
    $stmt = $conn->prepare("SELECT id FROM rent_ledger WHERE unit_id = ? AND tenant_id = ? AND year = ? AND month = ?");
    $stmt->execute([$unit_id, $tenant_id, $year, $month]);
    $existing_id = $stmt->fetchColumn();

    if ($existing_id) {
        // Update existing entry
        $stmt = $conn->prepare("UPDATE rent_ledger SET rent_due = ? WHERE id = ?");
        $stmt->execute([$rent_due, $existing_id]);
        $ledger_id = $existing_id;
    } else {
        // Create new entry
        $stmt = $conn->prepare("
            INSERT INTO rent_ledger (tenant_id, unit_id, year, month, rent_due, amount_paid, balance, status) 
            VALUES (?, ?, ?, ?, ?, 0.00, ?, 'unpaid')
        ");
        $balance = $rent_due; // Initial balance equals rent_due when no payments made
        $stmt->execute([$tenant_id, $unit_id, $year, $month, $rent_due, $balance]);
        $ledger_id = $conn->lastInsertId();
    }

    // Now recalculate based on existing payments
    recalc_ledger_from_payments($conn, $ledger_id);
    
    return $ledger_id;
}
?>