<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'auth/auth_check.php';
require 'config/db.php';

// fetch buildings (only those belonging to logged in agent)
$agent_id = $_SESSION['agent_id'];
$stmt = $conn->prepare("SELECT id, name FROM buildings WHERE agent_id = ?");
$stmt->execute([$agent_id]);
$buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch tenants (optional, can leave null)
$tenants = $conn->query("SELECT id, name FROM tenants ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Unit - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-4xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Add New Unit</h2>

  <form action="unit-save.php" method="POST" class="bg-white p-6 rounded shadow-md grid grid-cols-2 gap-6">
    
    <!-- Building -->
    <div class="col-span-2">
      <label class="block mb-2 font-medium">Building</label>
      <select name="building_id" required class="w-full border rounded p-2">
        <option value="">-- Select Building --</option>
        <?php foreach ($buildings as $b): ?>
          <option value="<?= $b['id']; ?>"><?= htmlspecialchars($b['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Unit Number -->
    <div>
      <label class="block mb-2 font-medium">Unit Number</label>
      <input type="text" name="unit_number" required class="w-full border rounded p-2">
    </div>

    <!-- Floor -->
    <div>
      <label class="block mb-2 font-medium">Floor</label>
      <input type="text" name="floor" class="w-full border rounded p-2">
    </div>

    <!-- Unit Type -->
    <div>
      <label class="block mb-2 font-medium">Unit Type</label>
      <select name="type" required class="w-full border rounded p-2">
        <option value="">-- Select Type --</option>
        <option value="shop">Shop</option>
        <option value="single room">Single Room</option>
        <option value="bedsitter">Bedsitter</option>
        <option value="studio">Studio</option>
        <option value="1 bedroom">1 Bedroom</option>
        <option value="2 bedroom">2 Bedroom</option>
        <option value="3 bedroom">3 Bedroom</option>
        <option value="standalone house">Standalone House</option>
      </select>
    </div>

    <!-- Rent -->
    <div>
      <label class="block mb-2 font-medium">Rent (KES)</label>
      <input type="number" step="0.01" name="rent" required class="w-full border rounded p-2">
    </div>

    <!-- Status -->
    <div>
      <label class="block mb-2 font-medium">Status</label>
      <select name="status" class="w-full border rounded p-2">
        <option value="vacant">Vacant</option>
        <option value="occupied">Occupied</option>
      </select>
    </div>

    <!-- Tenant (optional) -->
    <div class="col-span-2">
      <label class="block mb-2 font-medium">Tenant</label>
      <select name="tenant_id" class="w-full border rounded p-2">
        <option value="">-- None --</option>
        <?php foreach ($tenants as $t): ?>
          <option value="<?= $t['id']; ?>"><?= htmlspecialchars($t['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Submit -->
    <div class="col-span-2">
      <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Save Unit
      </button>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
