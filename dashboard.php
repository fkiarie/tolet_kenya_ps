<?php
require 'auth/auth_check.php';
require 'config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get current agent
$agent_id = $_SESSION['agent_id'];
echo $agent_id;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// ---- Summary Counts ----

// Occupied units
$stmt = $conn->query("SELECT COUNT(*) FROM units WHERE status='occupied'");
$occupied_units = $stmt->fetchColumn();

// Vacant units
$stmt = $conn->query("SELECT COUNT(*) FROM units WHERE status='vacant'");
$vacant_units = $stmt->fetchColumn();

// Total tenants
$stmt = $conn->query("SELECT COUNT(*) FROM tenants");
$total_tenants = $stmt->fetchColumn();

// Monthly payments (this month)
$stmt = $conn->prepare("
    SELECT SUM(amount_paid) 
    FROM payments 
    WHERE YEAR(payment_date) = YEAR(CURDATE()) 
      AND MONTH(payment_date) = MONTH(CURDATE())
");
$stmt->execute();
$monthly_payments = $stmt->fetchColumn() ?: 0;

// ---- Recent Payments ----
$stmt = $conn->query("
    SELECT p.amount_paid, p.payment_date, 
           t.name AS tenant_name, 
           u.unit_number, 
           b.name AS building_name
    FROM payments p
    JOIN tenants t ON p.tenant_id = t.id
    JOIN units u ON p.unit_id = u.id
    JOIN buildings b ON u.building_id = b.id
    ORDER BY p.payment_date DESC
    LIMIT 5
");
$recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- Payments Trend (Last 6 months) ----
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(amount_paid) as total
    FROM payments
    WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym
    ORDER BY ym
");
$stmt->execute();
$trend = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // key=ym, value=total

$labels = array_keys($trend);
$values = array_values($trend);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto p-6">

  <h2 class="text-xl font-semibold text-gray-700 mb-6">Dashboard Overview</h2>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Occupied Units</h3>
      <p class="text-2xl font-bold text-blue-600"><?= $occupied_units ?></p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Vacant Units</h3>
      <p class="text-2xl font-bold text-green-600"><?= $vacant_units ?></p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Total Tenants</h3>
      <p class="text-2xl font-bold text-purple-600"><?= $total_tenants ?></p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Monthly Payments</h3>
      <p class="text-2xl font-bold text-yellow-600">KES <?= number_format($monthly_payments, 2) ?></p>
    </div>
  </div>

  <!-- Recent Payments -->
  <div class="mt-10 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Payments</h3>
    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="px-4 py-2">Tenant</th>
          <th class="px-4 py-2">Unit</th>
          <th class="px-4 py-2">Amount</th>
          <th class="px-4 py-2">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($recent_payments) > 0): ?>
          <?php foreach ($recent_payments as $i => $p): ?>
            <tr class="<?= $i % 2 === 0 ? '' : 'bg-gray-50' ?>">
              <td class="px-4 py-2"><?= htmlspecialchars($p['tenant_name']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($p['unit_number']) ?> - <?= htmlspecialchars($p['building_name']) ?></td>
              <td class="px-4 py-2 text-green-600">KES <?= number_format($p['amount_paid'], 2) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($p['payment_date']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4" class="px-4 py-2 text-gray-600">No payments found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Payments Trend (Last 6 Months) -->
  <div class="mt-10 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Payments Trend (Last 6 Months)</h3>
    <canvas id="paymentsChart" height="100"></canvas>
  </div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('paymentsChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [{
      label: 'Total Payments (KES)',
      data: <?= json_encode($values) ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.6)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    }
  }
});
</script>
</body>
</html>
