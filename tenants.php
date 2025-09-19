<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Get current agent
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$has_photo = $_GET['has_photo'] ?? '';
$has_email = $_GET['has_email'] ?? '';

// Build the query with filters
$sql = "SELECT * FROM tenants WHERE agent_id = ?";
$params = [$agent_id];

// Add search filter
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR national_id LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Add date range filter
if (!empty($date_from)) {
    $sql .= " AND DATE(created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(created_at) <= ?";
    $params[] = $date_to;
}

// Add photo filter
if ($has_photo === '1') {
    $sql .= " AND passport_photo IS NOT NULL AND passport_photo != ''";
} elseif ($has_photo === '0') {
    $sql .= " AND (passport_photo IS NULL OR passport_photo = '')";
}

// Add email filter
if ($has_email === '1') {
    $sql .= " AND email IS NOT NULL AND email != ''";
} elseif ($has_email === '0') {
    $sql .= " AND (email IS NULL OR email = '')";
}

$sql .= " ORDER BY created_at DESC";

// Execute query
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for display
$total_tenants = count($tenants);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenants - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
  <div>
    <h2 class="text-2xl font-semibold">Tenants</h2>
    <p class="text-gray-600 mt-1">Total: <?php echo $total_tenants; ?> tenant<?php echo $total_tenants != 1 ? 's' : ''; ?></p>
  </div>
  <a href="tenant-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    + Add Tenant
  </a>
</div>

<?php if (isset($_GET['success'])): ?>
  <div id="success-alert" 
       class="mb-4 p-3 bg-green-100 text-green-800 rounded flex justify-between items-center">
    <span>Action completed successfully!</span>
    <button onclick="document.getElementById('success-alert').style.display='none';" 
            class="text-green-700 hover:text-green-900 font-bold ml-4">
      ✖
    </button>
  </div>
<?php endif; ?>

<!-- Filters Section -->
<div class="bg-white rounded shadow-md mb-6 p-4">
  <form method="GET" action="" class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-medium text-gray-900">Filter Tenants</h3>
      <?php if (!empty($search) || !empty($date_from) || !empty($date_to) || $has_photo !== '' || $has_email !== ''): ?>
        <a href="tenants.php" class="text-sm text-gray-500 hover:text-gray-700 underline">Clear all filters</a>
      <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
      <!-- Search Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <input type="text" 
               name="search" 
               value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Name, phone, email, ID..."
               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
      </div>

      <!-- Date From Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
        <input type="date" 
               name="date_from" 
               value="<?php echo htmlspecialchars($date_from); ?>"
               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
      </div>

      <!-- Date To Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
        <input type="date" 
               name="date_to" 
               value="<?php echo htmlspecialchars($date_to); ?>"
               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
      </div>

      <!-- Has Photo Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Photo Status</label>
        <select name="has_photo" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <option value="">All</option>
          <option value="1" <?php echo $has_photo === '1' ? 'selected' : ''; ?>>With Photo</option>
          <option value="0" <?php echo $has_photo === '0' ? 'selected' : ''; ?>>No Photo</option>
        </select>
      </div>

      <!-- Has Email Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email Status</label>
        <select name="has_email" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <option value="">All</option>
          <option value="1" <?php echo $has_email === '1' ? 'selected' : ''; ?>>With Email</option>
          <option value="0" <?php echo $has_email === '0' ? 'selected' : ''; ?>>No Email</option>
        </select>
      </div>
    </div>

    <div class="flex justify-between items-center pt-2">
      <div class="flex space-x-3">
        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
          Apply Filters
        </button>
        <button type="button" 
                onclick="toggleFilters()" 
                class="md:hidden bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
          <span class="desktop-hide-text">Hide Filters</span>
        </button>
      </div>
      
      <!-- Quick filter buttons -->
      <div class="flex space-x-2 text-sm">
        <a href="?has_photo=0" 
           class="px-3 py-1 bg-orange-100 text-orange-800 rounded hover:bg-orange-200 <?php echo $has_photo === '0' ? 'ring-2 ring-orange-500' : ''; ?>">
          Missing Photos
        </a>
        <a href="?has_email=0" 
           class="px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200 <?php echo $has_email === '0' ? 'ring-2 ring-red-500' : ''; ?>">
          Missing Emails
        </a>
        <a href="?date_from=<?php echo date('Y-m-d', strtotime('-30 days')); ?>" 
           class="px-3 py-1 bg-green-100 text-green-800 rounded hover:bg-green-200">
          Last 30 Days
        </a>
      </div>
    </div>
  </form>
</div>

<!-- Toggle filters button for mobile -->
<button id="toggle-filters" 
        onclick="toggleFilters()" 
        class="md:hidden mb-4 w-full bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">
  <span id="filter-toggle-text">Show Filters</span>
</button>

<!-- No results message -->
<?php if (empty($tenants) && (!empty($search) || !empty($date_from) || !empty($date_to) || $has_photo !== '' || $has_email !== '')): ?>
  <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
    <div class="flex">
      <div class="ml-3">
        <h3 class="text-sm font-medium text-yellow-800">No tenants found</h3>
        <p class="mt-1 text-sm text-yellow-700">
          No tenants match your current filters. Try adjusting your search criteria or 
          <a href="tenants.php" class="underline hover:text-yellow-900">clear all filters</a>.
        </p>
      </div>
    </div>
  </div>
<?php elseif (empty($tenants)): ?>
  <div class="bg-gray-50 border border-gray-200 rounded-md p-8 text-center">
    <h3 class="text-lg font-medium text-gray-900 mb-2">No tenants yet</h3>
    <p class="text-gray-600 mb-4">You haven't added any tenants to your account.</p>
    <a href="tenant-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Add Your First Tenant
    </a>
  </div>
<?php endif; ?>

<!-- Results table -->
<?php if (!empty($tenants)): ?>
<div class="overflow-x-auto">
  <table class="w-full bg-white rounded shadow-md">
    <thead>
      <tr class="bg-gray-100 text-left">
        <th class="p-3">Photo</th>
        <th class="p-3">
          <div class="flex items-center">
            Name
            <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name'])); ?>" 
               class="ml-1 text-gray-400 hover:text-gray-600">↕</a>
          </div>
        </th>
        <th class="p-3">Phone</th>
        <th class="p-3">Email</th>
        <th class="p-3">National ID</th>
        <th class="p-3">
          <div class="flex items-center">
            Added
            <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'date'])); ?>" 
               class="ml-1 text-gray-400 hover:text-gray-600">↕</a>
          </div>
        </th>
        <th class="p-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tenants as $tenant): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-3">
            <?php if ($tenant['passport_photo']): ?>
              <img src="uploads/<?php echo htmlspecialchars($tenant['passport_photo']); ?>" 
                   alt="Passport Photo" class="w-10 h-10 rounded-full object-cover">
            <?php else: ?>
              <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-gray-400 text-xs">No photo</span>
              </div>
            <?php endif; ?>
          </td>
          <td class="p-3">
            <div class="font-medium"><?php echo htmlspecialchars($tenant['name']); ?></div>
            <?php if ($tenant['next_of_kin']): ?>
              <div class="text-xs text-gray-500">Next of Kin: <?php echo htmlspecialchars($tenant['next_of_kin']); ?></div>
            <?php endif; ?>
          </td>
          <td class="p-3">
            <div><?php echo htmlspecialchars($tenant['phone']); ?></div>
            <?php if ($tenant['kin_contact']): ?>
              <div class="text-xs text-gray-500">Kin: <?php echo htmlspecialchars($tenant['kin_contact']); ?></div>
            <?php endif; ?>
          </td>
          <td class="p-3">
            <?php if ($tenant['email']): ?>
              <?php echo htmlspecialchars($tenant['email']); ?>
            <?php else: ?>
              <span class="text-gray-400 text-sm">No email</span>
            <?php endif; ?>
          </td>
          <td class="p-3 font-mono text-sm"><?php echo htmlspecialchars($tenant['national_id']); ?></td>
          <td class="p-3 text-sm text-gray-500">
            <?php echo date('M j, Y', strtotime($tenant['created_at'])); ?>
          </td>
          <td class="p-3">
            <div class="flex space-x-2 text-sm">
              <a href="tenant-view.php?id=<?php echo $tenant['id']; ?>" 
                 class="text-blue-600 hover:underline">View</a>
              <a href="tenant-edit.php?id=<?php echo $tenant['id']; ?>" 
                 class="text-green-600 hover:underline">Edit</a>
              <a href="tenant-delete.php?id=<?php echo $tenant['id']; ?>" 
                 onclick="return confirm('Are you sure you want to delete this tenant?');"
                 class="text-red-600 hover:underline">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<script>
function toggleFilters() {
  const filtersSection = document.querySelector('.bg-white.rounded.shadow-md.mb-6.p-4');
  const toggleText = document.getElementById('filter-toggle-text');
  const toggleButton = document.getElementById('toggle-filters');
  
  if (filtersSection.style.display === 'none') {
    filtersSection.style.display = 'block';
    toggleText.textContent = 'Hide Filters';
    toggleButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    toggleButton.classList.add('bg-gray-200', 'hover:bg-gray-300', 'text-gray-700');
  } else {
    filtersSection.style.display = 'none';
    toggleText.textContent = 'Show Filters';
    toggleButton.classList.remove('bg-gray-200', 'hover:bg-gray-300', 'text-gray-700');
    toggleButton.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white');
  }
}

// Auto-hide filters on mobile if no filters are active
document.addEventListener('DOMContentLoaded', function() {
  const hasActiveFilters = <?php echo json_encode(!empty($search) || !empty($date_from) || !empty($date_to) || $has_photo !== '' || $has_email !== ''); ?>;
  const toggleButton = document.getElementById('toggle-filters');
  const toggleText = document.getElementById('filter-toggle-text');
  
  if (window.innerWidth < 768 && !hasActiveFilters) {
    const filtersSection = document.querySelector('.bg-white.rounded.shadow-md.mb-6.p-4');
    filtersSection.style.display = 'none';
    toggleText.textContent = 'Show Filters';
    toggleButton.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white');
    toggleButton.classList.remove('bg-gray-200', 'hover:bg-gray-300', 'text-gray-700');
  }
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>