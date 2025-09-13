<?php
require 'auth/auth_check.php';
require 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<?php include 'includes/header.php'; ?>

  <h2 class="text-xl font-semibold text-gray-700 mb-6">Dashboard Overview</h2>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Occupied Units</h3>
      <p class="text-2xl font-bold text-blue-600">12</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Vacant Units</h3>
      <p class="text-2xl font-bold text-green-600">5</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Total Tenants</h3>
      <p class="text-2xl font-bold text-purple-600">17</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h3 class="text-gray-500 text-sm">Monthly Payments</h3>
      <p class="text-2xl font-bold text-yellow-600">KES 120,000</p>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="mt-10 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Payments</h3>
    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="px-4 py-2">Tenant</th>
          <th class="px-4 py-2">Unit</th>
          <th class="px-4 py-2">Amount</th>
          <th class="px-4 py-2">Date</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="px-4 py-2">John Doe</td>
          <td class="px-4 py-2">2 Bedroom - Building A</td>
          <td class="px-4 py-2 text-green-600">KES 20,000</td>
          <td class="px-4 py-2">2025-09-01</td>
        </tr>
        <tr class="bg-gray-50">
          <td class="px-4 py-2">Jane Smith</td>
          <td class="px-4 py-2">Bedsitter - Building B</td>
          <td class="px-4 py-2 text-green-600">KES 8,000</td>
          <td class="px-4 py-2">2025-09-02</td>
        </tr>
      </tbody>
    </table>
  </div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
