<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Get landlord by ID
if (!isset($_GET['id'])) {
    die("Landlord ID missing.");
}
$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM landlords WHERE id = ?");
$stmt->execute([$id]);
$landlord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$landlord) {
    die("Landlord not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Landlord - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-5xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Edit Landlord</h2>

  <form action="landlord-update.php" method="POST" enctype="multipart/form-data"
        class="bg-white p-6 rounded shadow-md grid grid-cols-2 gap-6">
    
    <input type="hidden" name="id" value="<?php echo $landlord['id']; ?>">

    <div>
      <label class="block mb-2 font-medium">Full Name</label>
      <input type="text" name="name" required 
             value="<?php echo htmlspecialchars($landlord['name']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">National ID</label>
      <input type="text" name="national_id" required 
             value="<?php echo htmlspecialchars($landlord['national_id']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Phone</label>
      <input type="text" name="phone" required 
             value="<?php echo htmlspecialchars($landlord['phone']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Email</label>
      <input type="email" name="email" 
             value="<?php echo htmlspecialchars($landlord['email']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Next of Kin</label>
      <input type="text" name="next_of_kin" 
             value="<?php echo htmlspecialchars($landlord['next_of_kin']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Next of Kin Contact</label>
      <input type="text" name="kin_contact" 
             value="<?php echo htmlspecialchars($landlord['kin_contact']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Bank Details</label>
      <input type="text" name="bank_details" 
             value="<?php echo htmlspecialchars($landlord['bank_details']); ?>"
             class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">ID Photo</label>
      <input type="file" name="id_photo" accept="image/*">
      <?php if ($landlord['id_photo']): ?>
        <img src="<?php echo $landlord['id_photo']; ?>" alt="ID Photo" class="mt-2 h-16">
      <?php endif; ?>
    </div>

    <div>
      <label class="block mb-2 font-medium">Passport Photo</label>
      <input type="file" name="passport_photo" accept="image/*">
      <?php if ($landlord['passport_photo']): ?>
        <img src="<?php echo $landlord['passport_photo']; ?>" alt="Passport" class="mt-2 h-16 rounded-full">
      <?php endif; ?>
    </div>

    <div class="col-span-2">
      <button type="submit" 
              class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Update Landlord
      </button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
