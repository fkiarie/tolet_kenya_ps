<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Ensure agent is logged in
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Get selected month & year
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

// Fetch payments belonging to this agent
$stmt = $conn->prepare("
    SELECT p.id AS payment_id, p.amount_paid, p.payment_date,
           rl.id AS ledger_id, rl.year, rl.month, rl.rent_due, rl.amount_paid AS ledger_paid, rl.balance AS ledger_balance, rl.status,
           u.unit_number, b.name AS building_name,
           t.name AS tenant_name, l.name AS landlord_name
    FROM payments p
    JOIN rent_ledger rl ON p.ledger_id = rl.id
    JOIN units u ON rl.unit_id = u.id
    JOIN buildings b ON u.building_id = b.id
    JOIN tenants t ON rl.tenant_id = t.id
    JOIN landlords l ON b.landlord_id = l.id
    WHERE p.agent_id = :agent_id
      AND rl.year = :year
      AND rl.month = :month
    ORDER BY b.name, u.unit_number, p.payment_date DESC
");
$stmt->execute([
    ':agent_id' => $agent_id,
    ':year'     => $year,
    ':month'    => $month
]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totals
$total_due     = 0;
$total_paid    = 0;
$total_balance = 0;
foreach ($payments as $p) {
    $total_due     += $p['rent_due'];
    $total_paid    += $p['ledger_paid'];
    $total_balance += $p['ledger_balance'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payments - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto p-6">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold">Payments</h2>
    <a href="payment-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      + Add Payment
    </a>
  </div>

  <!-- Filter Form -->
  <form method="GET" class="mb-6 flex space-x-4">
    <div>
      <label class="block text-sm font-medium">Year</label>
      <select name="year" class="border rounded p-2">
        <?php for($y = date('Y')-5; $y <= date('Y')+1; $y++): ?>
          <option value="<?= $y ?>" <?= $y==$year ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium">Month</label>
      <select name="month" class="border rounded p-2">
        <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?= $m ?>" <?= $m==$month ? 'selected':'' ?>>
            <?= date("F", mktime(0,0,0,$m,1)) ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="flex items-end">
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Filter</button>
    </div>
  </form>

  <!-- Payments Table -->
  <div class="overflow-x-auto bg-white rounded shadow">
    <table class="w-full border-collapse">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="px-4 py-2">Building</th>
          <th class="px-4 py-2">Unit</th>
          <th class="px-4 py-2">Tenant</th>
          <th class="px-4 py-2">Landlord</th>
          <th class="px-4 py-2">Rent Due</th>
          <th class="px-4 py-2">Paid (This Month)</th>
          <th class="px-4 py-2">Balance</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Payment Date</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($payments) > 0): ?>
          <?php foreach($payments as $row): ?>
          <tr class="border-b">
            <td class="px-4 py-2"><?= htmlspecialchars($row['building_name']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['unit_number']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['tenant_name']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['landlord_name']); ?></td>
            <td class="px-4 py-2"><?= number_format($row['rent_due'],2); ?></td>
            <td class="px-4 py-2"><?= number_format($row['ledger_paid'],2); ?></td>
            <td class="px-4 py-2 <?= $row['ledger_balance'] > 0 ? 'text-red-600 font-semibold':'' ?>">
              <?= number_format($row['ledger_balance'],2); ?>
            </td>
            <td class="px-4 py-2"><?= ucfirst($row['status']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['payment_date']); ?></td>
            <td class="px-4 py-2">
              <a href="payment-edit.php?id=<?= $row['payment_id'] ?>" class="text-blue-600 hover:underline">Edit</a> |
              <a href="payment-delete.php?id=<?= $row['payment_id'] ?>" class="text-red-600 hover:underline"
                 onclick="return confirm('Delete this payment?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="10" class="text-center py-4 text-gray-600">No payments found for this period.</td>
          </tr>
        <?php endif; ?>
      </tbody>
      <?php if (count($payments) > 0): ?>
      <tfoot class="bg-gray-100 font-semibold">
        <tr>
          <td colspan="4" class="px-4 py-2 text-right">Totals:</td>
          <td class="px-4 py-2"><?= number_format($total_due,2); ?></td>
          <td class="px-4 py-2"><?= number_format($total_paid,2); ?></td>
          <td class="px-4 py-2"><?= number_format($total_balance,2); ?></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
