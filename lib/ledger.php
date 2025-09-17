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
