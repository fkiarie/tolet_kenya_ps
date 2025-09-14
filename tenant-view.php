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
    <table class="w-full border border-gray-300 bg-white rounded shadow">
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
    <p class="text-gray-600">No units assigned yet.</p>
  <?php endif; ?>

  <!-- Assign New Unit -->
  <h3 class="text-xl font-semibold mt-8 mb-4">Assign New Unit</h3>

  <?php
  // Fetch vacant units
  $stmt = $conn->query("
      SELECT u.*, b.name AS building_name 
      FROM units u
      JOIN buildings b ON u.building_id = b.id
      WHERE u.status = 'vacant' AND u.tenant_id IS NULL
      ORDER BY b.name, u.unit_number
  ");
  $vacant_units = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <?php if (count($vacant_units) > 0): ?>
    <form action="unit-assign-save.php" method="POST" class="flex items-center space-x-4">
      <input type="hidden" name="tenant_id" value="<?php echo $tenant['id']; ?>">
      <select name="unit_id" required class="border rounded p-2">
        <option value="">-- Select Unit --</option>
        <?php foreach ($vacant_units as $unit): ?>
          <option value="<?php echo $unit['id']; ?>">
            <?php echo htmlspecialchars($unit['building_name'] . " - " . $unit['unit_number'] . " (" . $unit['type'] . ")"); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Assign Unit
      </button>
    </form>
  <?php else: ?>
    <p class="text-gray-600">No vacant units available.</p>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
