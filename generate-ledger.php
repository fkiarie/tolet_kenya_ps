<?php
// generate-ledger.php
require 'auth/auth_check.php'; // require admin or agent-level check if needed
require 'config/db.php';
require 'lib/ledger.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Provide year and month via GET (e.g., ?year=2025&month=10)
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

    // Determine previous month/year
    $prevYear = $year;
    $prevMonth = $month - 1;
    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

    // Fetch all occupied or relevant units that should have ledger rows:
    // We generate ledger for units that have tenant assigned OR that had previous balance
    $sql = "
      SELECT u.id as unit_id, u.rent, u.tenant_id, b.landlord_id
      FROM units u
      JOIN buildings b ON u.building_id = b.id
      WHERE u.tenant_id IS NOT NULL
      OR EXISTS (
          SELECT 1 FROM rent_ledger rl WHERE rl.unit_id = u.id AND rl.year = ? AND rl.month = ? AND rl.balance > 0
      )
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$prevYear, $prevMonth]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $conn->beginTransaction();
    try {
        foreach ($units as $u) {
            $unit_id = (int)$u['unit_id'];
            $tenant_id = $u['tenant_id'] ? (int)$u['tenant_id'] : null;
            $base_rent = (float)$u['rent'];

            // Check previous balance (if any)
            $stmtPrev = $conn->prepare("SELECT balance FROM rent_ledger WHERE unit_id = ? AND year = ? AND month = ?");
            $stmtPrev->execute([$unit_id, $prevYear, $prevMonth]);
            $prevBalance = (float)$stmtPrev->fetchColumn(); // 0 if none

            // New month rent_due = base_rent + max(prevBalance, 0)
            // If prevBalance is negative (credit), subtract it (i.e., base_rent + (-100) => base_rent - 100)
            $rent_due = round($base_rent + $prevBalance, 2);

            // If no tenant assigned, we can still create ledger rows (optionally)
            if (!$tenant_id) {
                // Skip if you prefer not to create ledger rows without tenants
                continue;
            }

            // Create or update ledger
            upsert_ledger($conn, $unit_id, $tenant_id, $year, $month, $rent_due);
        }

        $conn->commit();
        echo "Ledger generated for $year-$month\n";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
