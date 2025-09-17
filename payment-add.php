<?php
require 'auth/auth_check.php';
require 'config/db.php';
require 'lib/ledger.php';

$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    die("Unauthorized: Agent not in session.");
}

// get ledger_id from query if provided
$ledger_id = $_GET['ledger_id'] ?? null;
$ledger = null;
$ledgers = [];

if ($ledger_id) {
    // fetch ledger row to confirm it belongs to this agent
    $stmt = $conn->prepare("
        SELECT rl.id, rl.year, rl.month, rl.rent_due, rl.amount_paid, rl.balance, rl.status,
               t.name AS tenant_name, u.unit_number, b.name AS building_name
        FROM rent_ledger rl
        JOIN units u ON rl.unit_id = u.id
        JOIN tenants t ON rl.tenant_id = t.id
        JOIN buildings b ON u.building_id = b.id
        JOIN landlords l ON b.landlord_id = l.id
        WHERE rl.id = ? AND l.agent_id = ?
    ");
    $stmt->execute([$ledger_id, $agent_id]);
    $ledger = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ledger) {
        die("Ledger not found or not accessible.");
    }
} else {
    // load available ledgers for this agent
    $stmt = $conn->prepare("
        SELECT rl.id, rl.year, rl.month, rl.rent_due, rl.amount_paid, rl.balance, rl.status,
               t.name AS tenant_name, u.unit_number, b.name AS building_name
        FROM rent_ledger rl
        JOIN units u ON rl.unit_id = u.id
        JOIN tenants t ON rl.tenant_id = t.id
        JOIN buildings b ON u.building_id = b.id
        JOIN landlords l ON b.landlord_id = l.id
        WHERE l.agent_id = ?
        ORDER BY b.name, u.unit_number
    ");
    $stmt->execute([$agent_id]);
    $ledgers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Payment</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
  <h2 class="text-2xl font-semibold mb-6">Add Payment</h2>

  <form action="payment-save.php" method="POST" class="space-y-4">
    <?php if ($ledger): ?>
      <input type="hidden" name="ledger_id" value="<?= $ledger['id'] ?>">

      <div>
        <label class="block text-sm font-medium">Ledger</label>
        <p class="p-2 border rounded bg-gray-50">
          <?= htmlspecialchars($ledger['building_name']) ?> - Unit <?= htmlspecialchars($ledger['unit_number']) ?> 
          (<?= htmlspecialchars($ledger['tenant_name']) ?>) 
          — <?= $ledger['month'] ?>/<?= $ledger['year'] ?>
        </p>
      </div>
    <?php else: ?>
      <div>
        <label class="block text-sm font-medium">Select Ledger</label>
        <select name="ledger_id" required class="border rounded p-2 w-full">
          <option value="">-- Choose --</option>
          <?php foreach ($ledgers as $l): ?>
            <option value="<?= $l['id'] ?>">
              <?= htmlspecialchars($l['building_name']) ?> - Unit <?= htmlspecialchars($l['unit_number']) ?> 
              (<?= htmlspecialchars($l['tenant_name']) ?>) — <?= $l['month'] ?>/<?= $l['year'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <div>
      <label class="block text-sm font-medium">Amount Paid</label>
      <input type="number" step="0.01" name="amount_paid" required class="border rounded p-2 w-full">
    </div>

    <div>
      <label class="block text-sm font-medium">Payment Date</label>
      <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required class="border rounded p-2 w-full">
    </div>

    <div class="flex justify-end space-x-3">
      <a href="payments.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</a>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Payment</button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
