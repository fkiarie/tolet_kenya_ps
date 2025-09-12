<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <form action="register-process.php" method="POST" class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">Agent Registration</h2>
    
    <label class="block mb-2 text-gray-600">Full Name</label>
    <input type="text" name="name" class="w-full px-4 py-2 border rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    
    <label class="block mb-2 text-gray-600">Email</label>
    <input type="email" name="email" class="w-full px-4 py-2 border rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    
    <label class="block mb-2 text-gray-600">Phone</label>
    <input type="text" name="phone" class="w-full px-4 py-2 border rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    
    <label class="block mb-2 text-gray-600">Password</label>
    <input type="password" name="password" class="w-full px-4 py-2 border rounded mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    
    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Register</button>
  </form>
</body>
</html>
