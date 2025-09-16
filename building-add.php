<?php
require 'auth/auth_check.php';
require 'config/db.php';

// get logged-in agent id (support both keys)
$sessionAgentId = $_SESSION['agent_id'] ?? $_SESSION['user_id'] ?? null;
if (!$sessionAgentId) {
    header("Location: login-form.php");
    exit;
}

// fetch agent name to display in the select
$agentStmt = $conn->prepare("SELECT id, name FROM agents WHERE id = ?");
$agentStmt->execute([$sessionAgentId]);
$agent = $agentStmt->fetch(PDO::FETCH_ASSOC);

// fetch landlords belonging to this agent
$landlordStmt = $conn->prepare("SELECT id, name FROM landlords WHERE agent_id = ? ORDER BY name ASC");
$landlordStmt->execute([$sessionAgentId]);
$landlords = $landlordStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Building - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-3xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Add New Building</h2>

  <!-- Show error/success messages -->
  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <form action="building-save.php" method="POST" class="bg-white p-6 rounded shadow-md grid grid-cols-2 gap-6">
    <!-- Agent select (readonly current agent) -->
    <div class="col-span-2">
      <label class="block mb-2 font-medium">Agent (you)</label>
      <select name="agent_id" required class="w-full border rounded p-2 bg-gray-100" readonly>
        <option value="<?php echo htmlspecialchars($agent['id']); ?>">
          <?php echo htmlspecialchars($agent['name']) . ' (You)'; ?>
        </option>
      </select>
    </div>

    <div>
      <label class="block mb-2 font-medium">Building Name</label>
      <input type="text" name="name" required class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">County</label>
      <input type="text" name="county" required class="w-full border rounded p-2">
    </div>

    <div class="col-span-2">
      <label class="block mb-2 font-medium">Landlord</label>
      <select name="landlord_id" required class="w-full border rounded p-2">
        <option value="">-- Select Landlord --</option>
        <?php foreach ($landlords as $landlord): ?>
          <option value="<?php echo $landlord['id']; ?>">
            <?php echo htmlspecialchars($landlord['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-span-2">
      <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Save Building
      </button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
