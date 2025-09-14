<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Get current agent
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Fetch units with building and tenant info
$sql = "SELECT u.*, b.name AS building_name, t.name AS tenant_name
        FROM units u
        JOIN buildings b ON u.building_id = b.id
        LEFT JOIN tenants t ON u.tenant_id = t.id
        WHERE b.agent_id = :agent_id
        ORDER BY b.name, u.unit_number";
$stmt = $conn->prepare($sql);
$stmt->execute([':agent_id' => $agent_id]);
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Units - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
  <h2 class="text-2xl font-semibold">Units</h2>
  <a href="unit-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    + Add Unit
  </a>
</div>

<?php if (isset($_GET['success'])): ?>
  <div id="success-alert" 
       class="mb-4 p-3 bg-green-100 text-green-800 rounded flex justify-between items-center">
    <span>Action completed successfully!</span>
    <button onclick="document.getElementById('success-alert').style.display='none';" 
            class="text-green-700 hover:text-green-900 font-bold ml-4">
      ✖
    </button>
  </div>
<?php endif; ?>

<div class="overflow-x-auto">
  <table class="w-full bg-white rounded shadow-md">
    <thead>
      <tr class="bg-gray-100 text-left">
        <th class="p-3">Building</th>
        <th class="p-3">Unit #</th>
        <th class="p-3">Floor</th>
        <th class="p-3">Type</th>
        <th class="p-3">Rent</th>
        <th class="p-3">Status</th>
        <th class="p-3">Tenant</th>
        <th class="p-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($units as $unit): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-3"><?php echo htmlspecialchars($unit['building_name']); ?></td>
          <td class="p-3 font-medium"><?php echo htmlspecialchars($unit['unit_number']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($unit['floor']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($unit['type']); ?></td>
          <td class="p-3">KES <?php echo number_format($unit['rent'], 2); ?></td>
          <td class="p-3">
            <?php if ($unit['status'] === 'vacant'): ?>
              <span class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded">Vacant</span>
            <?php else: ?>
              <span class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded">Occupied</span>
            <?php endif; ?>
          </td>
          <td class="p-3">
            <?php echo $unit['tenant_name'] ? htmlspecialchars($unit['tenant_name']) : '-'; ?>
          </td>
          <td class="p-3">
            <a href="unit-assign.php?id=<?php echo $unit['id']; ?>" class="text-green-600 hover:underline">Assign</a>|
            <a href="unit-edit.php?id=<?php echo $unit['id']; ?>" class="text-blue-600 hover:underline">Edit</a> | 
            <a href="unit-delete.php?id=<?php echo $unit['id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this unit?');"
               class="text-red-600 hover:underline">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
