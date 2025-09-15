<?php
require 'auth/auth_check.php';
require 'config/db.php';

// get selected month & year
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

// fetch ledger rows for month
$stmt = $conn->prepare("
  SELECT rl.*, u.unit_number, b.name AS building_name, t.name AS tenant_name, l.name AS landlord_name
  FROM rent_ledger rl
  JOIN units u ON rl.unit_id = u.id
  JOIN buildings b ON u.building_id = b.id
  JOIN tenants t ON rl.tenant_id = t.id
  JOIN landlords l ON b.landlord_id = l.id
  WHERE rl.year = :year AND rl.month = :month
  ORDER BY b.name, u.unit_number
");
$stmt->execute([':year'=>$year, ':month'=>$month]);
$ledgers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// totals
$total_due=0; $total_paid=0; $total_balance=0;
foreach($ledgers as $r){
  $total_due += $r['rent_due'];
  $total_paid += $r['amount_paid'];
  $total_balance += $r['balance'];
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
          <th class="px-4 py-2">Amount Paid</th>
          <th class="px-4 py-2">Balance</th>
          <th class="px-4 py-2">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($ledgers) > 0): ?>
          <?php foreach($ledgers as $row): ?>
          <tr class="border-b">
            <td class="px-4 py-2"><?= htmlspecialchars($row['building_name']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['unit_number']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['tenant_name']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['landlord_name']); ?></td>
            <td class="px-4 py-2"><?= number_format($row['rent_due'],2); ?></td>
            <td class="px-4 py-2"><?= number_format($row['amount_paid'],2); ?></td>
            <td class="px-4 py-2 <?= $row['balance'] > 0 ? 'text-red-600 font-semibold':'' ?>">
              <?= number_format($row['balance'],2); ?>
            </td>
            <td class="px-4 py-2">
              <?= $row['balance'] > 0 ? 'Pending' : 'Cleared'; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center py-4 text-gray-600">No payments found for this period.</td>
          </tr>
        <?php endif; ?>
      </tbody>
      <?php if (count($ledgers) > 0): ?>
      <tfoot class="bg-gray-100 font-semibold">
        <tr>
          <td colspan="4" class="px-4 py-2 text-right">Totals:</td>
          <td class="px-4 py-2"><?= number_format($total_due,2); ?></td>
          <td class="px-4 py-2"><?= number_format($total_paid,2); ?></td>
          <td class="px-4 py-2"><?= number_format($total_balance,2); ?></td>
          <td></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
