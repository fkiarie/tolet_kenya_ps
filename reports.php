<?php
require 'auth/auth_check.php';
require 'config/db.php';

$agent_id = $_SESSION['agent_id'] ?? null;

// Get optional year filter
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch commission rate
$stmt = $conn->prepare("SELECT commission_rate FROM agents WHERE id=?");
$stmt->execute([$agent_id]);
$commission_rate = (float)$stmt->fetchColumn();

// Fetch monthly rent collections
$stmt2 = $conn->prepare("
    SELECT YEAR(p.payment_date) AS year, MONTH(p.payment_date) AS month,
           SUM(p.amount_paid) AS total_collected
    FROM payments p
    WHERE p.agent_id = :agent_id AND YEAR(p.payment_date) = :year
    GROUP BY YEAR(p.payment_date), MONTH(p.payment_date)
    ORDER BY YEAR(p.payment_date), MONTH(p.payment_date)
");
$stmt2->execute([
    ':agent_id' => $agent_id,
    ':year' => $year
]);
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Commission Report - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-5xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Commission Report (<?= $year ?>)</h2>

  <!-- Year Filter -->
  <form method="GET" class="mb-6">
    <label class="font-medium">Select Year:</label>
    <select name="year" class="border rounded p-2 ml-2" onchange="this.form.submit()">
      <?php for($y = date('Y')-5; $y <= date('Y'); $y++): ?>
        <option value="<?= $y ?>" <?= $y==$year ? 'selected':'' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
  </form>

  <div class="overflow-x-auto bg-white rounded shadow">
    <table class="w-full border-collapse">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="px-4 py-2">Month</th>
          <th class="px-4 py-2">Rent Collected</th>
          <th class="px-4 py-2">Commission Rate</th>
          <th class="px-4 py-2">Commission Earned</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($rows) > 0): ?>
          <?php 
          $total_collected = 0; 
          $total_commission = 0;
          foreach ($rows as $r): 
            $commission = $r['total_collected'] * ($commission_rate / 100);
            $total_collected += $r['total_collected'];
            $total_commission += $commission;
          ?>
          <tr class="border-b">
            <td class="px-4 py-2"><?= date("F", mktime(0,0,0,$r['month'],1)) ?></td>
            <td class="px-4 py-2"><?= number_format($r['total_collected'],2) ?></td>
            <td class="px-4 py-2"><?= $commission_rate ?>%</td>
            <td class="px-4 py-2 text-green-600 font-semibold"><?= number_format($commission,2) ?></td>
          </tr>
          <?php endforeach; ?>
          <tr class="bg-gray-100 font-semibold">
            <td class="px-4 py-2 text-right">TOTAL:</td>
            <td class="px-4 py-2"><?= number_format($total_collected,2) ?></td>
            <td></td>
            <td class="px-4 py-2 text-green-700"><?= number_format($total_commission,2) ?></td>
          </tr>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center py-4 text-gray-600">No payments recorded this year.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
