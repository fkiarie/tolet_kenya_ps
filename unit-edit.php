<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Get unit ID
if (!isset($_GET['id'])) {
    header("Location: units.php");
    exit;
}
$id = $_GET['id'];

// Fetch unit details
$stmt = $conn->prepare("SELECT * FROM units WHERE id = ?");
$stmt->execute([$id]);
$unit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$unit) {
    die("Unit not found.");
}

// Fetch buildings for dropdown (only buildings of logged-in agent)
$stmt = $conn->prepare("SELECT * FROM buildings WHERE agent_id = ?");
$stmt->execute([$_SESSION['agent_id']]);
$buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Unit - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-4xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Edit Unit</h2>

  <form action="unit-update.php" method="POST" 
        class="bg-white p-6 rounded shadow-md grid grid-cols-2 gap-6">

    <input type="hidden" name="id" value="<?php echo $unit['id']; ?>">

    <!-- Building -->
    <div class="col-span-2">
      <label class="block mb-2 font-medium">Building</label>
      <select name="building_id" required class="w-full border rounded p-2">
        <?php foreach ($buildings as $building): ?>
          <option value="<?php echo $building['id']; ?>" 
            <?php echo ($building['id'] == $unit['building_id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($building['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Unit Number -->
    <div>
      <label class="block mb-2 font-medium">Unit Number</label>
      <input type="text" name="unit_number" required 
             value="<?php echo htmlspecialchars($unit['unit_number']); ?>"
             class="w-full border rounded p-2">
    </div>

    <!-- Floor -->
    <div>
      <label class="block mb-2 font-medium">Floor</label>
      <input type="text" name="floor" 
             value="<?php echo htmlspecialchars($unit['floor']); ?>"
             class="w-full border rounded p-2">
    </div>

    <!-- Unit Type -->
    <div>
      <label class="block mb-2 font-medium">Unit Type</label>
      <select name="type" required class="w-full border rounded p-2">
        <?php 
        $types = ['shop','single room','bedsitter','studio','1 bedroom','2 bedroom','3 bedroom','standalone house'];
        foreach ($types as $type): ?>
          <option value="<?php echo $type; ?>" 
            <?php echo ($unit['type'] == $type) ? 'selected' : ''; ?>>
            <?php echo ucfirst($type); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Rent -->
    <div>
      <label class="block mb-2 font-medium">Rent</label>
      <input type="number" step="0.01" name="rent" required 
             value="<?php echo htmlspecialchars($unit['rent']); ?>"
             class="w-full border rounded p-2">
    </div>

    <!-- Status -->
    <div>
      <label class="block mb-2 font-medium">Status</label>
      <select name="status" class="w-full border rounded p-2">
        <option value="vacant" <?php echo ($unit['status'] == 'vacant') ? 'selected' : ''; ?>>Vacant</option>
        <option value="occupied" <?php echo ($unit['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
      </select>
    </div>

    <!-- Submit -->
    <div class="col-span-2">
      <button type="submit" 
              class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Update Unit
      </button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
