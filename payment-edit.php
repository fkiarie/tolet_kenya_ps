<?php
require 'auth/auth_check.php';
require 'config/db.php';

$agent_id = $_SESSION['agent_id'] ?? null;
$payment_id = (int)($_GET['id'] ?? 0);

if (!$payment_id) {
    die("Payment not specified.");
}

$stmt = $conn->prepare("
    SELECT p.*, t.name AS tenant_name, u.unit_number, b.name AS building_name
    FROM payments p
    JOIN tenants t ON p.tenant_id = t.id
    JOIN units u ON p.unit_id = u.id
    JOIN buildings b ON u.building_id = b.id
    WHERE p.id = ? AND p.agent_id = ?
");
$stmt->execute([$payment_id, $agent_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Payment not found or not accessible.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Payment - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded shadow">
  <h2 class="text-xl font-semibold mb-4">Edit Payment</h2>
  <form method="POST" action="payment-update.php">
    <input type="hidden" name="id" value="<?= $payment['id'] ?>">
    <input type="hidden" name="ledger_id" value="<?= $payment['ledger_id'] ?>">

    <p class="mb-2 text-gray-700">
      Tenant: <strong><?= htmlspecialchars($payment['tenant_name']) ?></strong><br>
      Unit: <?= htmlspecialchars($payment['unit_number']) ?>, 
      Building: <?= htmlspecialchars($payment['building_name']) ?>
    </p>

    <label class="block mb-2">Amount Paid</label>
    <input type="number" step="0.01" name="amount_paid" value="<?= $payment['amount_paid'] ?>" class="w-full border p-2 rounded mb-4" required>

    <label class="block mb-2">Payment Date</label>
    <input type="date" name="payment_date" value="<?= $payment['payment_date'] ?>" class="w-full border p-2 rounded mb-4" required>

    <div class="flex justify-between">
      <a href="payments.php" class="px-4 py-2 bg-gray-400 text-white rounded">Cancel</a>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
