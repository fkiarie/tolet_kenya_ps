<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// get current file name
$currentPage = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$logout_path = ($current_dir === 'auth') ? 'logout.php' : '/auth/logout.php';
?>

<!-- Navbar -->
<nav class="bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg px-6 py-4 flex justify-between items-center relative z-50">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-md">
            <span class="text-blue-600 font-bold text-lg">🏠</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Tolet Kenya</h1>
        <span class="text-blue-200 text-sm font-medium bg-blue-700 px-3 py-1 rounded-full">Agent Portal</span>
    </div>

    <div class="flex items-center space-x-6">
        <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2">
            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                <span class="text-white text-sm">👤</span>
            </div>
            <div class="flex flex-col">
                <span class="text-blue-100 text-xs">Welcome back,</span>
                <strong class="text-white text-sm"><?php echo $_SESSION['user_name'] ?? 'Agent'; ?></strong>
            </div>
        </div>

        <a href="<?php echo $logout_path; ?>"
            class="group px-5 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg flex items-center space-x-2 transform hover:scale-105">
            <span>Logout</span>
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
        </a>
    </div>
</nav>

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-72 bg-white shadow-xl border-r border-gray-200 flex flex-col sticky top-0 h-screen">


        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto p-4">
            <ul class="space-y-2">
                <?php
                $links = [
                    "dashboard.php" => ["🏠", "Dashboard"],
                    "landlords.php" => ["👤", "Landlords"],
                    "buildings.php" => ["🏢", "Buildings"],
                    "units.php"     => ["🏠", "Units"],
                    "tenants.php"   => ["🔑", "Tenants"],
                    "payments.php"  => ["💰", "Payments"],
                    "reports.php"   => ["📊", "Reports"],
                    "agent-profile.php" => ["⚙️", "My Profile"]
                ];
                foreach ($links as $file => [$icon, $label]): ?>
                    <li>
                        <a href="<?= $file ?>"
                            class="group flex items-center px-4 py-3 rounded-xl transition-all duration-200 
                            <?= $currentPage == $file
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-200'
                                : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <span class="text-xl mr-3 <?= $currentPage == $file
                                                            ? 'opacity-100'
                                                            : 'opacity-60 group-hover:opacity-100'; ?>">
                                <?= $icon ?>
                            </span>
                            <span class="font-medium"><?= $label ?></span>
                            <?php if ($currentPage == $file): ?>
                                <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
            <div class="text-center">
                <p class="text-xs text-gray-500 mb-1">Property Management</p>
                <p class="text-xs font-medium text-gray-700">Tolet Kenya v2.0</p>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <main class="flex-1 p-8 bg-gray-50 overflow-y-auto">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span>🏠</span>
                <span>/</span>
                <span class="capitalize font-medium text-blue-600">
                    <?php echo str_replace('.php', '', $currentPage); ?>
                </span>
            </div>
        </div>