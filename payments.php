<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Ensure agent is logged in
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Get selected month, year, and building
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$building_id = isset($_GET['building_id']) ? (int)$_GET['building_id'] : null;

// Fetch buildings for this agent (for the dropdown)
$buildings_stmt = $conn->prepare("
    SELECT DISTINCT b.id, b.name 
    FROM buildings b
    JOIN units u ON b.id = u.building_id
    JOIN rent_ledger rl ON u.id = rl.unit_id
    JOIN payments p ON rl.id = p.ledger_id
    WHERE p.agent_id = :agent_id
    ORDER BY b.name
");
$buildings_stmt->execute([':agent_id' => $agent_id]);
$buildings = $buildings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build the payments query with optional building filter
$sql = "
    SELECT p.id AS payment_id, p.amount_paid, p.payment_date,
           rl.id AS ledger_id, rl.year, rl.month, rl.rent_due, rl.amount_paid AS ledger_paid, rl.balance AS ledger_balance, rl.status,
           u.unit_number, b.id AS building_id, b.name AS building_name,
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
";

$params = [
    ':agent_id' => $agent_id,
    ':year'     => $year,
    ':month'    => $month
];

// Add building filter if selected
if ($building_id) {
    $sql .= " AND b.id = :building_id";
    $params[':building_id'] = $building_id;
}

$sql .= " ORDER BY b.name, u.unit_number, p.payment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
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
  <form method="GET" class="mb-6 flex flex-wrap gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Year</label>
      <select name="year" class="border rounded p-2 min-w-20">
        <?php for($y = date('Y')-5; $y <= date('Y')+1; $y++): ?>
          <option value="<?= $y ?>" <?= $y==$year ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    
    <div>
      <label class="block text-sm font-medium mb-1">Month</label>
      <select name="month" class="border rounded p-2 min-w-32">
        <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?= $m ?>" <?= $m==$month ? 'selected':'' ?>>
            <?= date("F", mktime(0,0,0,$m,1)) ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>
    
    <div>
      <label class="block text-sm font-medium mb-1">Building</label>
      <select name="building_id" class="border rounded p-2 min-w-48">
        <option value="">All Buildings</option>
        <?php foreach($buildings as $building): ?>
          <option value="<?= $building['id'] ?>" <?= $building['id']==$building_id ? 'selected':'' ?>>
            <?= htmlspecialchars($building['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    
    <div class="flex items-end">
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Filter</button>
    </div>
    
    <?php if ($building_id || $year != date('Y') || $month != date('n')): ?>
    <div class="flex items-end">
      <a href="?" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Clear Filters</a>
    </div>
    <?php endif; ?>
  </form>

  <!-- Filter Summary -->
  <?php if ($building_id || count($payments) > 0): ?>
  <div class="mb-4 p-3 bg-blue-50 rounded border">
    <p class="text-sm text-blue-700">
      <strong>Showing:</strong> 
      <?= date("F Y", mktime(0,0,0,$month,1,$year)) ?>
      <?php if ($building_id): ?>
        <?php 
        $selected_building = array_filter($buildings, function($b) use ($building_id) { 
          return $b['id'] == $building_id; 
        });
        $selected_building = reset($selected_building);
        ?>
        • Building: <strong><?= htmlspecialchars($selected_building['name']) ?></strong>
      <?php else: ?>
        • <strong>All Buildings</strong>
      <?php endif; ?>
      • <strong><?= count($payments) ?></strong> payment(s) found
    </p>
  </div>
  <?php endif; ?>

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
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2"><?= htmlspecialchars($row['building_name']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['unit_number']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['tenant_name']); ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['landlord_name']); ?></td>
            <td class="px-4 py-2"><?= number_format($row['rent_due'],2); ?></td>
            <td class="px-4 py-2"><?= number_format($row['ledger_paid'],2); ?></td>
            <td class="px-4 py-2 <?= $row['ledger_balance'] > 0 ? 'text-red-600 font-semibold':'text-green-600' ?>">
              <?= number_format($row['ledger_balance'],2); ?>
            </td>
            <td class="px-4 py-2">
              <span class="px-2 py-1 rounded text-xs <?= 
                $row['status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                ($row['status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') 
              ?>">
                <?= ucfirst($row['status']); ?>
              </span>
            </td>
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
            <td colspan="10" class="text-center py-8 text-gray-600">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="font-medium">No payments found</p>
                <p class="text-sm">Try adjusting your filters or check a different time period.</p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
      <?php if (count($payments) > 0): ?>
      <tfoot class="bg-gray-100 font-semibold">
        <tr>
          <td colspan="4" class="px-4 py-2 text-right">Totals:</td>
          <td class="px-4 py-2"><?= number_format($total_due,2); ?></td>
          <td class="px-4 py-2"><?= number_format($total_paid,2); ?></td>
          <td class="px-4 py-2 <?= $total_balance > 0 ? 'text-red-600':'text-green-600' ?>">
            <?= number_format($total_balance,2); ?>
          </td>
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