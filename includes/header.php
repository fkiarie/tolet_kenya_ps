<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// get current file name
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Navbar -->
<nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-600">Tolet Kenya</h1>
    <div class="flex items-center space-x-4">
        <span class="text-gray-600">
            Welcome, <strong><?php echo $_SESSION['user_name'] ?? 'Agent'; ?></strong>
        </span>
        <a href="http://localhost/tolet_kenya_ps/auth/login-form.php" 
   class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
   Logout
</a>
    </div>
</nav>

<div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md h-screen p-6">
        <ul class="space-y-4">
            <li>
                <a href="dashboard.php"
                    class="block px-4 py-2 rounded <?php echo $currentPage == 'dashboard.php' ? 'bg-blue-600 text-white' : 'hover:bg-blue-100'; ?>">
                    🏠 Dashboard
                </a>
            </li>
            <li>
                <a href="landlords.php"
                    class="block px-4 py-2 rounded <?php echo $currentPage == 'landlords.php' ? 'bg-blue-600 text-white' : 'hover:bg-blue-100'; ?>">
                    👤 Landlords
                </a>
            </li>
            <li>
                <a href="buildings.php"
                    class="block px-4 py-2 rounded <?php echo $currentPage == 'buildings.php' ? 'bg-blue-600 text-white' : 'hover:bg-blue-100'; ?>">
                    🏢 Buildings
                </a>
            </li>
            <li>
                <a href="tenants.php"
                    class="block px-4 py-2 rounded <?php echo $currentPage == 'tenants.php' ? 'bg-blue-600 text-white' : 'hover:bg-blue-100'; ?>">
                    🔑 Tenants
                </a>
            </li>
            <li>
                <a href="payments.php"
                    class="block px-4 py-2 rounded <?php echo $currentPage == 'payments.php' ? 'bg-blue-600 text-white' : 'hover:bg-blue-100'; ?>">
                    💰 Payments
                </a>
            </li>
            <li>
                <a href="reports.php"
                    class="block px-4 py-2 rounded <?php echo $currentPage == 'reports.php' ? 'bg-blue-600 text-white' : 'hover:bg-blue-100'; ?>">
                    📊 Reports
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Wrapper starts here -->
    <main class="flex-1 p-6">