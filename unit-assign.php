<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Get unit ID
if (!isset($_GET['id'])) {
    header("Location: units.php");
    exit;
}
$unit_id = $_GET['id'];

// Fetch unit
$stmt = $conn->prepare("SELECT * FROM units WHERE id = ?");
$stmt->execute([$unit_id]);
$unit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$unit) {
    die("Unit not found.");
}

// Fetch tenants for dropdown
$stmt = $conn->query("SELECT id, name, phone FROM tenants ORDER BY name ASC");
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Tenant - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'includes/header.php'; ?>

<div class="max-w-2xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Assign Tenant to Unit</h2>

  <form action="unit-assign-save.php" method="POST" class="bg-white p-6 rounded shadow-md space-y-4">
    <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">

    <div>
      <label class="block mb-2 font-medium">Unit</label>
      <input type="text" value="<?php echo htmlspecialchars($unit['unit_number'] . ' - ' . $unit['type']); ?>" 
             disabled class="w-full border rounded p-2 bg-gray-100">
    </div>

    <div>
      <label class="block mb-2 font-medium">Select Tenant</label>
      <select name="tenant_id" required class="w-full border rounded p-2">
        <option value="">-- Choose Tenant --</option>
        <?php foreach ($tenants as $tenant): ?>
          <option value="<?php echo $tenant['id']; ?>"
            <?php echo ($tenant['id'] == $unit['tenant_id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($tenant['name']) . " (" . $tenant['phone'] . ")"; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="block mb-2 font-medium">Status</label>
      <select name="status" class="w-full border rounded p-2">
        <option value="vacant" <?php echo ($unit['status'] == 'vacant') ? 'selected' : ''; ?>>Vacant</option>
        <option value="occupied" <?php echo ($unit['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
      </select>
    </div>

    <div>
      <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Save Assignment
      </button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
