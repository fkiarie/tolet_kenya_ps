<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Get current agent
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Fetch tenants that belong to this agent
$stmt = $conn->prepare("
    SELECT t.*
    FROM tenants t
    JOIN units u ON u.tenant_id = t.id
    JOIN buildings b ON u.building_id = b.id
    JOIN landlords l ON b.landlord_id = l.id
    WHERE l.agent_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$agent_id]);
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenants - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
  <h2 class="text-2xl font-semibold">Tenants</h2>
  <a href="tenant-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    + Add Tenant
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
        <th class="p-3">Photo</th>
        <th class="p-3">Name</th>
        <th class="p-3">Phone</th>
        <th class="p-3">Email</th>
        <th class="p-3">National ID</th>
        <th class="p-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tenants as $tenant): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-3">
            <?php if ($tenant['passport_photo']): ?>
              <img src="uploads/<?php echo htmlspecialchars($tenant['passport_photo']); ?>" 
                   alt="Passport Photo" class="w-10 h-10 rounded-full object-cover">
            <?php else: ?>
              <span class="text-gray-400">No photo</span>
            <?php endif; ?>
          </td>
          <td class="p-3 font-medium"><?php echo htmlspecialchars($tenant['name']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($tenant['phone']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($tenant['email']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($tenant['national_id']); ?></td>
          <td class="p-3">
            <a href="tenant-view.php?id=<?php echo $tenant['id']; ?>" class="text-blue-600 hover:underline">View</a> |</a>
            <a href="tenant-edit.php?id=<?php echo $tenant['id']; ?>" class="text-blue-600 hover:underline">Edit</a> | 
            <a href="tenant-delete.php?id=<?php echo $tenant['id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this tenant?');"
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
