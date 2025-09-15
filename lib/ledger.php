<?php
// lib/ledger.php
require_once __DIR__ . '/../config/db.php'; // adjust path as necessary

/**
 * Ensure session started and return agent id
 */
function current_agent_id() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['agent_id'] ?? $_SESSION['user_id'] ?? null;
}

/**
 * Create or update a ledger row for given unit-year-month.
 * Returns ledger_id.
 *
 * $conn is PDO instance.
 */
function upsert_ledger(PDO $conn, int $unit_id, int $tenant_id, int $year, int $month, float $rent_due) {
    // Use transaction outside as caller may combine operations
    // If row exists update rent_due if changed, otherwise insert
    $sql = "SELECT id, rent_due, amount_paid FROM rent_ledger WHERE unit_id = ? AND year = ? AND month = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$unit_id, $year, $month]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $ledger_id = (int)$row['id'];
        // If rent_due changed (e.g. carry-forward applied) update it
        if (floatval($row['rent_due']) != floatval($rent_due)) {
            $update = $conn->prepare("UPDATE rent_ledger SET rent_due = ?, balance = rent_due - amount_paid WHERE id = ?");
            $update->execute([$rent_due, $ledger_id]);
        }
        return $ledger_id;
    } else {
        $balance = $rent_due; // initially unpaid
        $ins = $conn->prepare("INSERT INTO rent_ledger (tenant_id, unit_id, year, month, rent_due, amount_paid, balance, status) VALUES (?, ?, ?, ?, ?, 0, ?, 'unpaid')");
        $ins->execute([$tenant_id, $unit_id, $year, $month, $rent_due, $balance]);
        return $conn->lastInsertId();
    }
}

/**
 * Recalculate ledger totals from payments (safe to call after any payment insert/update/delete)
 */
function recalc_ledger_from_payments(PDO $conn, int $ledger_id) {
    // Sum payments for the ledger
    $stmt = $conn->prepare("SELECT IFNULL(SUM(amount_paid),0) AS paid FROM payments WHERE ledger_id = ?");
    $stmt->execute([$ledger_id]);
    $paid = (float)$stmt->fetchColumn();

    // Get rent_due
    $stmt2 = $conn->prepare("SELECT rent_due FROM rent_ledger WHERE id = ?");
    $stmt2->execute([$ledger_id]);
    $rent_due = (float)$stmt2->fetchColumn();

    $balance = round($rent_due - $paid, 2);
    $status = $balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

    $upd = $conn->prepare("UPDATE rent_ledger SET amount_paid = ?, balance = ?, status = ? WHERE id = ?");
    $upd->execute([$paid, $balance, $status, $ledger_id]);

    return ['amount_paid' => $paid, 'balance' => $balance, 'status' => $status];
}
