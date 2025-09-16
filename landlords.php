<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'auth/auth_check.php';
require 'config/db.php';

// Get current agent
$agent_id = $_SESSION['agent_id'] ?? null;
//echo $agent_id;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Fetch only landlords that belong to this agent
$stmt = $conn->prepare("SELECT * FROM landlords WHERE agent_id = :agent_id ORDER BY created_at DESC");
$stmt->execute(['agent_id' => $agent_id]);
$landlords = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Landlords - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
  <h2 class="text-2xl font-semibold">Landlords</h2>
  <a href="landlord-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    + Add Landlord
  </a>
</div>

<?php if (isset($_GET['success'])): ?>
  <div id="success-alert" 
       class="mb-4 p-3 bg-green-100 text-green-800 rounded flex justify-between items-center">
    <span>Landlord added successfully!</span>
    <button onclick="document.getElementById('success-alert').style.display='none';" 
            class="text-green-700 hover:text-green-900 font-bold ml-4">
      ✖
    </button>
  </div>
<?php endif; ?>

<!-- Toggle Buttons -->
<div class="mb-4 flex space-x-3">
  <button onclick="toggleView('table')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">📋 Table View</button>
  <button onclick="toggleView('card')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">🖼️ Card View</button>
</div>

<!-- Table View -->
<div id="tableView" class="bg-white rounded shadow-md overflow-hidden">
  <table class="w-full">
    <thead>
      <tr class="bg-gray-100 text-left">
        <th class="p-3">Photo</th>
        <th class="p-3">Name</th>
        <th class="p-3">Phone</th>
        <th class="p-3">Email</th>
        <th class="p-3">National ID</th>
        <th class="p-3">Next of Kin</th>
        <th class="p-3">Bank Details</th>
        <th class="p-3">Created</th>
        <th class="p-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($landlords as $landlord): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-3">
            <?php if (!empty($landlord['passport_photo'])): ?>
              <img src="<?php echo htmlspecialchars($landlord['passport_photo']); ?>" 
                   alt="Passport" class="w-12 h-12 rounded-full object-cover">
            <?php else: ?>
              <span class="text-gray-400">No photo</span>
            <?php endif; ?>
          </td>
          <td class="p-3 font-medium"><?php echo htmlspecialchars($landlord['name']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['phone']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['email']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['national_id']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['next_of_kin'] . " (" . $landlord['kin_contact'] . ")"); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['bank_details']); ?></td>
          <td class="p-3 text-sm text-gray-500"><?php echo date("M d, Y", strtotime($landlord['created_at'])); ?></td>
          <td class="p-3">
            <a href="landlord-edit.php?id=<?php echo $landlord['id']; ?>" class="text-blue-600 hover:underline">Edit</a> | 
            <a href="landlord-delete.php?id=<?php echo $landlord['id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this landlord?');"
               class="text-red-600 hover:underline">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Card View -->
<div id="cardView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php foreach ($landlords as $landlord): ?>
    <div class="bg-white p-4 rounded shadow hover:shadow-lg transition">
      <div class="flex items-center space-x-4">
        <?php if (!empty($landlord['passport_photo'])): ?>
          <img src="<?php echo htmlspecialchars($landlord['passport_photo']); ?>" 
               alt="Passport" class="w-16 h-16 rounded-full object-cover">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">N/A</div>
        <?php endif; ?>
        <div>
          <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($landlord['name']); ?></h3>
          <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($landlord['phone']); ?></p>
        </div>
      </div>
      <div class="mt-4 text-sm text-gray-700 space-y-1">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($landlord['email']); ?></p>
        <p><strong>ID:</strong> <?php echo htmlspecialchars($landlord['national_id']); ?></p>
        <p><strong>Next of Kin:</strong> <?php echo htmlspecialchars($landlord['next_of_kin']); ?> (<?php echo htmlspecialchars($landlord['kin_contact']); ?>)</p>
        <p><strong>Bank:</strong> <?php echo htmlspecialchars($landlord['bank_details']); ?></p>
        <p class="text-gray-500 text-xs">Created: <?php echo date("M d, Y", strtotime($landlord['created_at'])); ?></p>
      </div>
      <div class="mt-4 flex space-x-3">
        <a href="landlord-edit.php?id=<?php echo $landlord['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
        <a href="landlord-delete.php?id=<?php echo $landlord['id']; ?>" 
           onclick="return confirm('Are you sure you want to delete this landlord?');"
           class="text-red-600 hover:underline">Delete</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
  function toggleView(view) {
    document.getElementById('tableView').classList.add('hidden');
    document.getElementById('cardView').classList.add('hidden');
    if (view === 'table') {
      document.getElementById('tableView').classList.remove('hidden');
    } else {
      document.getElementById('cardView').classList.remove('hidden');
    }
  }
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
