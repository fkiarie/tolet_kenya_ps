<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
  <!-- Left: Image -->
  <div class="w-1/2 bg-cover" style="background-image: url('../assets/images/login-page.png');"></div>

  <!-- Right: Login Form -->
  <div class="w-1/2 flex items-center justify-center bg-white">
    <form action="login-process.php" method="POST" class="w-3/4 max-w-md">
      <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">Agent Login</h2>
      
      <label class="block mb-2 text-gray-600">Email</label>
      <input type="email" name="email" class="w-full px-4 py-2 border rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      
      <label class="block mb-2 text-gray-600">Password</label>
      <input type="password" name="password" class="w-full px-4 py-2 border rounded mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
    </form>
  </div>
</div>
</body>
</html>
