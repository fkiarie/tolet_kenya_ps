<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
  <!-- Navbar -->
  <nav class="bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-3">
      <span class="text-2xl">🏠</span>
      <h1 class="text-xl md:text-2xl font-bold text-white">Tolet Kenya</h1>
    </div>
    <div>
      <a href="auth/login-form.php" 
         class="px-4 py-2 text-sm font-medium text-white hover:underline transition">
        Login
      </a>
      <a href="auth/register-form.php" 
         class="ml-4 px-5 py-2 bg-white text-blue-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
        Register
      </a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="relative bg-gradient-to-r from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-6 lg:px-12 py-20 lg:py-32 flex flex-col lg:flex-row items-center">
      <!-- Text -->
      <div class="lg:w-1/2 mb-10 lg:mb-0 text-center lg:text-left">
        <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight">
          Welcome to <span class="text-blue-600">Tolet Kenya</span>
        </h2>
        <p class="mt-6 text-lg text-gray-600">
          Your trusted property management solution. Manage landlords, tenants, payments, and reports all in one place.
        </p>
        <div class="mt-8 flex justify-center lg:justify-start space-x-4">
          <a href="auth/register-form.php" 
             class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg shadow-md hover:bg-blue-700 transition">
            Get Started
          </a>
          <a href="auth/login-form.php" 
             class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg shadow hover:bg-gray-50 transition">
            Login
          </a>
        </div>
      </div>

      <!-- Illustration -->
      <div class="lg:w-1/2 flex justify-center">
        <img src="https://cdn-icons-png.flaticon.com/512/619/619034.png" 
             alt="Property Illustration" 
             class="w-80 md:w-96 drop-shadow-lg">
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-white border-t mt-10 py-6 text-center text-sm text-gray-500">
    © <?php echo date('Y'); ?> Tolet Kenya. All Rights Reserved.
  </footer>
</body>
</html>
