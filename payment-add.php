<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Defaults
$year = date('Y');
$month = date('n');
$message = "";

// Fetch assigned tenants + units
$stmt = $conn->query("
    SELECT u.id as unit_id, u.unit_number, b.name AS building_name, t.id as tenant_id, t.name AS tenant_name, u.rent
    FROM units u
    JOIN buildings b ON u.building_id = b.id
    JOIN tenants t ON u.tenant_id = t.id
    WHERE u.status = 'occupied'
    ORDER BY b.name, u.unit_number
");
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = $_POST['tenant_id'];
    $unit_id   = $_POST['unit_id'];
    $amount    = (float) $_POST['amount_paid'];
    $year      = (int) $_POST['year'];
    $month     = (int) $_POST['month'];

    // fetch or create ledger row
    $stmt = $conn->prepare("SELECT * FROM rent_ledger WHERE tenant_id=? AND unit_id=? AND year=? AND month=?");
    $stmt->execute([$tenant_id, $unit_id, $year, $month]);
    $ledger = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ledger) {
        // create ledger row (new month)
        // get unit rent
        $stmtU = $conn->prepare("SELECT rent FROM units WHERE id=?");
        $stmtU->execute([$unit_id]);
        $rent = $stmtU->fetchColumn();

        $stmtI = $conn->prepare("INSERT INTO rent_ledger (tenant_id, unit_id, year, month, rent_due, amount_paid, balance)
                                 VALUES (?,?,?,?,?,?,?)");
        $stmtI->execute([$tenant_id, $unit_id, $year, $month, $rent, 0, $rent]);
        $ledger_id = $conn->lastInsertId();

        $stmt = $conn->prepare("SELECT * FROM rent_ledger WHERE id=?");
        $stmt->execute([$ledger_id]);
        $ledger = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // update ledger
    $new_paid    = $ledger['amount_paid'] + $amount;
    $new_balance = $ledger['rent_due'] - $new_paid;

    $stmtU = $conn->prepare("UPDATE rent_ledger SET amount_paid=?, balance=? WHERE id=?");
    $stmtU->execute([$new_paid, $new_balance, $ledger['id']]);

    $message = "Payment recorded successfully.";

    // if still balance and month ended, it will carry forward in next month ledger
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Payment - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-3xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Add Payment</h2>

  <?php if ($message): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="bg-white p-6 rounded shadow space-y-4">
    <!-- Tenant + Unit -->
    <div>
      <label class="block mb-2 font-medium">Tenant & Unit</label>
      <select name="tenant_id" required class="border rounded p-2 w-full"
              onchange="updateUnit(this)">
        <option value="">-- Select Tenant & Unit --</option>
        <?php foreach ($assignments as $a): ?>
          <option value="<?= $a['tenant_id'] ?>"
                  data-unit="<?= $a['unit_id'] ?>"
                  data-rent="<?= $a['rent'] ?>">
            <?= htmlspecialchars($a['tenant_name']." - ".$a['building_name']." ".$a['unit_number']." (Rent: ".$a['rent'].")") ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="unit_id" id="unit_id">
    </div>

    <!-- Year & Month -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block mb-2 font-medium">Year</label>
        <select name="year" class="border rounded p-2 w-full">
          <?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?>
            <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div>
        <label class="block mb-2 font-medium">Month</label>
        <select name="month" class="border rounded p-2 w-full">
          <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= date("F", mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <!-- Amount Paid -->
    <div>
      <label class="block mb-2 font-medium">Amount Paid</label>
      <input type="number" step="0.01" name="amount_paid" required class="border rounded p-2 w-full">
    </div>

    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Save Payment
    </button>
  </form>
</div>

<script>
function updateUnit(select){
  var opt = select.options[select.selectedIndex];
  document.getElementById('unit_id').value = opt.dataset.unit;
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
