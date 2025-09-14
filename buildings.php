<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Fetch all buildings with landlord name
$stmt = $conn->query("
  SELECT b.*, l.name AS landlord_name 
  FROM buildings b
  JOIN landlords l ON b.landlord_id = l.id
  ORDER BY b.created_at DESC
");
$buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buildings - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
  <h2 class="text-xl font-semibold">Buildings</h2>
  <a href="building-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    + Add Building
  </a>
</div>

<?php if (isset($_GET['success'])): ?>
  <div id="success-alert" 
       class="mb-4 p-3 bg-green-100 text-green-800 rounded flex justify-between items-center">
    <span>
      <?php echo $_GET['success'] === 'added' ? 'Building added successfully!' : 
                  ($_GET['success'] === 'updated' ? 'Building updated!' : 'Building deleted!'); ?>
    </span>
    <button onclick="document.getElementById('success-alert').style.display='none';" 
            class="text-green-700 hover:text-green-900 font-bold ml-4">
      ✖
    </button>
  </div>
<?php endif; ?>

<table class="w-full bg-white rounded shadow-md">
  <thead>
    <tr class="bg-gray-100 text-left">
      <th class="p-3">Building Name</th>
      <th class="p-3">County</th>
      <th class="p-3">Landlord</th>
      <th class="p-3">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($buildings as $building): ?>
      <tr class="border-b">
        <td class="p-3"><?php echo htmlspecialchars($building['name']); ?></td>
        <td class="p-3"><?php echo htmlspecialchars($building['county']); ?></td>
        <td class="p-3"><?php echo htmlspecialchars($building['landlord_name']); ?></td>
        <td class="p-3">
          <a href="building-edit.php?id=<?php echo $building['id']; ?>" 
             class="text-blue-600 hover:underline">Edit</a> | 
          <a href="building-delete.php?id=<?php echo $building['id']; ?>" 
             onclick="return confirm('Delete this building?');"
             class="text-red-600 hover:underline">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
</body>
</html>
