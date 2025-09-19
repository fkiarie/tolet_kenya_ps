<?php
require 'auth/auth_check.php';
require 'config/db.php';

$agent_id = $_SESSION['user_id'] ?? null;

if (!$agent_id) {
    header("Location: auth/login-form.php");
    exit;
}

// Fetch agent details
$stmt = $conn->prepare("SELECT * FROM agents WHERE id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agent) {
    die("Agent not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $commission_rate = $_POST['commission_rate'];

    // Validate commission rate
    if (!is_numeric($commission_rate) || $commission_rate < 0 || $commission_rate > 100) {
        header("Location: agent-profile.php?error=Commission rate must be between 0 and 100");
        exit;
    }

    // Update query
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE agents SET name=?, email=?, phone=?, password=?, commission_rate=? WHERE id=?";
        $params = [$name, $email, $phone, $hashed, $commission_rate, $agent_id];
    } else {
        $sql = "UPDATE agents SET name=?, email=?, phone=?, commission_rate=? WHERE id=?";
        $params = [$name, $email, $phone, $commission_rate, $agent_id];
    }

    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        $_SESSION['user_name'] = $name; // refresh name in session
        header("Location: agent-profile.php?success=Details updated successfully");
        exit;
    } else {
        header("Location: agent-profile.php?error=Update failed");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-2xl mx-auto mt-8 bg-white p-6 rounded-lg shadow">
    <h2 class="text-2xl font-semibold mb-6">Edit Profile</h2>

    <?php if (isset($_GET['success'])): ?>
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-800 rounded"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-600">Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($agent['name']); ?>" required
                   class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-gray-600">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($agent['email']); ?>" required
                   class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-gray-600">Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($agent['phone'] ?? ''); ?>"
                   class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-gray-600">Commission Rate (%) <span class="text-sm text-gray-500">(0-100)</span></label>
            <input type="number" name="commission_rate" 
                   value="<?php echo htmlspecialchars($agent['commission_rate'] ?? '10.00'); ?>" 
                   min="0" max="100" step="0.01" required
                   class="w-full border p-2 rounded">
            <p class="text-sm text-gray-500 mt-1">Enter your commission rate as a percentage (e.g., 10.50 for 10.5%)</p>
        </div>
        <div>
            <label class="block text-gray-600">Password <span class="text-sm text-gray-500">(leave blank to keep current)</span></label>
            <input type="password" name="password" class="w-full border p-2 rounded">
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>