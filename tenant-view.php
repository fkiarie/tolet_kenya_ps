<?php
require 'auth/auth_check.php';
require 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: tenants.php");
    exit;
}
$tenant_id = $_GET['id'];

// Fetch tenant
$stmt = $conn->prepare("SELECT * FROM tenants WHERE id = ?");
$stmt->execute([$tenant_id]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenant) {
    die("Tenant not found.");
}

// Fetch units assigned to this tenant
$stmt = $conn->prepare("
    SELECT u.*, b.name AS building_name 
    FROM units u 
    JOIN buildings b ON u.building_id = b.id
    WHERE u.tenant_id = ?
");
$stmt->execute([$tenant_id]);
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch ledger rows + payments for this tenant
$stmt = $conn->prepare("
    SELECT rl.*, u.unit_number, b.name AS building_name
    FROM rent_ledger rl
    JOIN units u ON rl.unit_id = u.id
    JOIN buildings b ON u.building_id = b.id
    WHERE rl.tenant_id = ?
    ORDER BY rl.year DESC, rl.month DESC
");
$stmt->execute([$tenant_id]);
$ledgers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group payments per ledger
$ledger_ids = array_column($ledgers, 'id');
$payments_by_ledger = [];
if ($ledger_ids) {
    $in  = str_repeat('?,', count($ledger_ids)-1) . '?';
    $stmt = $conn->prepare("SELECT * FROM payments WHERE ledger_id IN ($in) ORDER BY payment_date ASC");
    $stmt->execute($ledger_ids);
    $all_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_payments as $p) {
        $payments_by_ledger[$p['ledger_id']][] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant - <?php echo htmlspecialchars($tenant['name']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'includes/header.php'; ?>

<div class="max-w-5xl mx-auto p-6">
  <!-- Alerts -->
  <?php if (isset($_GET['success'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded relative">
      <?php echo htmlspecialchars($_GET['success']); ?>
      <button onclick="this.parentElement.remove();" class="absolute top-0 right-0 px-2">✖</button>
    </div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded relative">
      <?php echo htmlspecialchars($_GET['error']); ?>
      <button onclick="this.parentElement.remove();" class="absolute top-0 right-0 px-2">✖</button>
    </div>
  <?php endif; ?>

  <h2 class="text-2xl font-semibold mb-6">Tenant Details</h2>

  <!-- Tenant Info -->
  <div class="bg-white shadow rounded p-6 mb-8 space-y-3">
    <p><strong>Name:</strong> <?php echo htmlspecialchars($tenant['name']); ?></p>
    <p><strong>National ID:</strong> <?php echo htmlspecialchars($tenant['national_id']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
    <p><strong>Next of Kin:</strong> <?php echo htmlspecialchars($tenant['next_of_kin']); ?> (<?php echo htmlspecialchars($tenant['kin_contact']); ?>)</p>
  </div>

  <!-- Assigned Units -->
  <h3 class="text-xl font-semibold mb-4">Assigned Units</h3>
  <?php if (count($units) > 0): ?>
    <table class="w-full border border-gray-300 bg-white rounded shadow mb-8">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-4 py-2">Building</th>
          <th class="px-4 py-2">Unit Number</th>
          <th class="px-4 py-2">Type</th>
          <th class="px-4 py-2">Floor</th>
          <th class="px-4 py-2">Rent</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($units as $unit): ?>
        <tr>
          <td class="border px-4 py-2"><?php echo htmlspecialchars($unit['building_name']); ?></td>
          <td class="border px-4 py-2"><?php echo htmlspecialchars($unit['unit_number']); ?></td>
          <td class="border px-4 py-2"><?php echo htmlspecialchars($unit['type']); ?></td>
          <td class="border px-4 py-2"><?php echo htmlspecialchars($unit['floor']); ?></td>
          <td class="border px-4 py-2"><?php echo number_format($unit['rent'], 2); ?></td>
          <td class="border px-4 py-2"><?php echo htmlspecialchars($unit['status']); ?></td>
          <td class="border px-4 py-2">
            <form action="unit-unassign.php" method="POST" onsubmit="return confirm('Unassign this unit?');">
              <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
              <input type="hidden" name="tenant_id" value="<?php echo $tenant['id']; ?>">
              <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Unassign</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="text-gray-600 mb-8">No units assigned yet.</p>
  <?php endif; ?>

  <!-- Payment History -->
  <h3 class="text-xl font-semibold mb-4">Payment History</h3>
  <?php if (count($ledgers) > 0): ?>
    <table class="w-full border border-gray-300 bg-white rounded shadow">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-3 py-2">Period</th>
          <th class="px-3 py-2">Building</th>
          <th class="px-3 py-2">Unit</th>
          <th class="px-3 py-2">Rent Due</th>
          <th class="px-3 py-2">Paid</th>
          <th class="px-3 py-2">Balance</th>
          <th class="px-3 py-2">Payments</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ledgers as $ledger): ?>
        <tr class="border-b">
          <td class="px-3 py-2"><?php echo $ledger['month']."/".$ledger['year']; ?></td>
          <td class="px-3 py-2"><?php echo htmlspecialchars($ledger['building_name']); ?></td>
          <td class="px-3 py-2"><?php echo htmlspecialchars($ledger['unit_number']); ?></td>
          <td class="px-3 py-2"><?php echo number_format($ledger['rent_due'],2); ?></td>
          <td class="px-3 py-2"><?php echo number_format($ledger['amount_paid'],2); ?></td>
          <td class="px-3 py-2"><?php echo number_format($ledger['balance'],2); ?></td>
          <td class="px-3 py-2">
            <?php if (!empty($payments_by_ledger[$ledger['id']])): ?>
              <ul class="list-disc list-inside text-sm text-gray-700">
                <?php foreach ($payments_by_ledger[$ledger['id']] as $p): ?>
                  <li><?php echo $p['payment_date']." - ".number_format($p['amount'],2); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <span class="text-gray-500 text-sm">No payments</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="text-gray-600">No payment records yet.</p>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
