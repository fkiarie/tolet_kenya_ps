<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Fetch tenant by ID
if (!isset($_GET['id'])) {
    header("Location: tenants.php");
    exit;
}
$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM tenants WHERE id = ?");
$stmt->execute([$id]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenant) {
    header("Location: tenants.php?error=notfound");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Tenant - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-4xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Edit Tenant</h2>

  <form action="tenant-update.php" method="POST" enctype="multipart/form-data" 
        class="bg-white p-6 rounded shadow-md grid grid-cols-2 gap-6">

    <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">

    <div>
      <label class="block mb-2 font-medium">Full Name</label>
      <input type="text" name="name" required value="<?php echo htmlspecialchars($tenant['name']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">National ID</label>
      <input type="text" name="national_id" required value="<?php echo htmlspecialchars($tenant['national_id']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Phone</label>
      <input type="text" name="phone" required value="<?php echo htmlspecialchars($tenant['phone']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($tenant['email']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Next of Kin</label>
      <input type="text" name="next_of_kin" value="<?php echo htmlspecialchars($tenant['next_of_kin']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Next of Kin Contact</label>
      <input type="text" name="kin_contact" value="<?php echo htmlspecialchars($tenant['kin_contact']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Current ID Photo</label>
      <?php if ($tenant['id_photo']): ?>
        <img src="uploads/<?php echo htmlspecialchars($tenant['id_photo']); ?>" 
             class="w-20 h-20 object-cover mb-2 rounded border">
      <?php else: ?>
        <p class="text-gray-500">No ID photo uploaded</p>
      <?php endif; ?>
      <input type="file" name="id_photo" accept="image/*" class="w-full">
    </div>

    <div>
      <label class="block mb-2 font-medium">Current Passport Photo</label>
      <?php if ($tenant['passport_photo']): ?>
        <img src="uploads/<?php echo htmlspecialchars($tenant['passport_photo']); ?>" 
             class="w-20 h-20 object-cover mb-2 rounded border">
      <?php else: ?>
        <p class="text-gray-500">No passport photo uploaded</p>
      <?php endif; ?>
      <input type="file" name="passport_photo" accept="image/*" class="w-full">
    </div>

    <div class="col-span-2">
      <button type="submit" 
              class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Update Tenant
      </button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
