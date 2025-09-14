<?php
require 'auth/auth_check.php';
require 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Tenant - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-4xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-6">Add New Tenant</h2>

  <form action="tenant-save.php" method="POST" enctype="multipart/form-data" 
        class="bg-white p-6 rounded shadow-md grid grid-cols-2 gap-6">

    <div>
      <label class="block mb-2 font-medium">Full Name</label>
      <input type="text" name="name" required class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">National ID</label>
      <input type="text" name="national_id" required class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Phone</label>
      <input type="text" name="phone" required class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Email</label>
      <input type="email" name="email" class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Next of Kin</label>
      <input type="text" name="next_of_kin" class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">Next of Kin Contact</label>
      <input type="text" name="kin_contact" class="w-full border rounded p-2">
    </div>

    <div>
      <label class="block mb-2 font-medium">ID Photo</label>
      <input type="file" name="id_photo" accept="image/*" class="w-full">
    </div>

    <div>
      <label class="block mb-2 font-medium">Passport Photo</label>
      <input type="file" name="passport_photo" accept="image/*" class="w-full">
    </div>

    <div class="col-span-2">
      <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Save Tenant
      </button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
