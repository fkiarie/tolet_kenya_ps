<?php

require 'auth/auth_check.php';
require 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get current agent
$agent_id = $_SESSION['agent_id'];
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$has_photo = $_GET['has_photo'] ?? '';

// Build the query with filters
$sql = "SELECT * FROM landlords WHERE agent_id = :agent_id";
$params = ['agent_id' => $agent_id];

// Add search filter
if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR phone LIKE :search OR email LIKE :search OR national_id LIKE :search OR next_of_kin LIKE :search)";
    $params['search'] = "%{$search}%";
}

// Add date range filter
if (!empty($date_from)) {
    $sql .= " AND DATE(created_at) >= :date_from";
    $params['date_from'] = $date_from;
}
if (!empty($date_to)) {
    $sql .= " AND DATE(created_at) <= :date_to";
    $params['date_to'] = $date_to;
}

// Add photo filter
if ($has_photo === 'yes') {
    $sql .= " AND passport_photo IS NOT NULL AND passport_photo != ''";
} elseif ($has_photo === 'no') {
    $sql .= " AND (passport_photo IS NULL OR passport_photo = '')";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$landlords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for stats
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM landlords WHERE agent_id = :agent_id");
$count_stmt->execute(['agent_id' => $agent_id]);
$total_landlords = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Landlords - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
  <div>
    <h2 class="text-2xl font-semibold">Landlords</h2>
    <p class="text-gray-600 text-sm">
      Showing <?php echo count($landlords); ?> of <?php echo $total_landlords; ?> landlords
    </p>
  </div>
  <a href="landlord-add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    + Add Landlord
  </a>
</div>

<?php if (isset($_GET['success'])): ?>
  <div id="success-alert" 
       class="mb-4 p-3 bg-green-100 text-green-800 rounded flex justify-between items-center">
    <span>Landlord added successfully!</span>
    <button onclick="document.getElementById('success-alert').style.display='none';" 
            class="text-green-700 hover:text-green-900 font-bold ml-4">
      ✖
    </button>
  </div>
<?php endif; ?>

<!-- Filters Section -->
<div class="bg-white p-4 rounded shadow-md mb-6">
  <div class="flex justify-between items-center mb-4">
    <h3 class="text-lg font-semibold">Filters</h3>
    <button onclick="toggleFilters()" class="text-blue-600 hover:text-blue-800" id="filterToggle">
      Hide Filters
    </button>
  </div>
  
  <form method="GET" class="space-y-4" id="filtersForm">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Search Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Name, phone, email, ID..."
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Date From Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Date To Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Photo Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Has Photo</label>
        <select name="has_photo" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">All</option>
          <option value="yes" <?php echo $has_photo === 'yes' ? 'selected' : ''; ?>>With Photo</option>
          <option value="no" <?php echo $has_photo === 'no' ? 'selected' : ''; ?>>Without Photo</option>
        </select>
      </div>
    </div>

    <!-- Filter Actions -->
    <div class="flex space-x-3 pt-4 border-t">
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
        Apply Filters
      </button>
      <a href="?" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
        Clear All
      </a>
      <button type="button" onclick="exportData()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
        Export CSV
      </button>
    </div>
  </form>
</div>

<!-- Toggle Buttons -->
<div class="mb-4 flex justify-between items-center">
  <div class="flex space-x-3">
    <button onclick="toggleView('table')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" id="tableBtn">📋 Table View</button>
    <button onclick="toggleView('card')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" id="cardBtn">🖼️ Card View</button>
  </div>
  
  <!-- Quick Stats -->
  <div class="text-sm text-gray-600">
    Total: <?php echo count($landlords); ?> landlords
  </div>
</div>

<!-- No Results Message -->
<?php if (empty($landlords)): ?>
  <div class="bg-white p-8 rounded shadow text-center">
    <div class="text-gray-500 mb-4">
      <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No landlords found</h3>
    <p class="text-gray-500">
      <?php if (!empty($search) || !empty($date_from) || !empty($date_to) || !empty($has_photo)): ?>
        Try adjusting your filters or <a href="?" class="text-blue-600 hover:underline">clear all filters</a>
      <?php else: ?>
        Get started by adding your first landlord.
      <?php endif; ?>
    </p>
    <a href="landlord-add.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Add First Landlord
    </a>
  </div>
<?php else: ?>

<!-- Table View -->
<div id="tableView" class="bg-white rounded shadow-md overflow-hidden overflow-x-auto">
  <table class="w-full min-w-full">
    <thead>
      <tr class="bg-gray-100 text-left">
        <th class="p-3">Photo</th>
        <th class="p-3">Name</th>
        <th class="p-3">Phone</th>
        <th class="p-3">Email</th>
        <th class="p-3">National ID</th>
        <th class="p-3">Next of Kin</th>
        <th class="p-3">Bank Details</th>
        <th class="p-3">Created</th>
        <th class="p-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($landlords as $landlord): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-3">
            <?php if (!empty($landlord['passport_photo'])): ?>
              <img src="<?php echo htmlspecialchars($landlord['passport_photo']); ?>" 
                   alt="Passport" class="w-12 h-12 rounded-full object-cover">
            <?php else: ?>
              <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 text-xs">
                No Photo
              </div>
            <?php endif; ?>
          </td>
          <td class="p-3 font-medium"><?php echo htmlspecialchars($landlord['name']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['phone']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['email']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['national_id']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['next_of_kin'] . " (" . $landlord['kin_contact'] . ")"); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($landlord['bank_details']); ?></td>
          <td class="p-3 text-sm text-gray-500"><?php echo date("M d, Y", strtotime($landlord['created_at'])); ?></td>
          <td class="p-3">
            <a href="landlord-edit.php?id=<?php echo $landlord['id']; ?>" class="text-blue-600 hover:underline">Edit</a> | 
            <a href="landlord-delete.php?id=<?php echo $landlord['id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this landlord?');"
               class="text-red-600 hover:underline">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Card View -->
<div id="cardView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php foreach ($landlords as $landlord): ?>
    <div class="bg-white p-4 rounded shadow hover:shadow-lg transition">
      <div class="flex items-center space-x-4">
        <?php if (!empty($landlord['passport_photo'])): ?>
          <img src="<?php echo htmlspecialchars($landlord['passport_photo']); ?>" 
               alt="Passport" class="w-16 h-16 rounded-full object-cover">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">N/A</div>
        <?php endif; ?>
        <div>
          <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($landlord['name']); ?></h3>
          <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($landlord['phone']); ?></p>
        </div>
      </div>
      <div class="mt-4 text-sm text-gray-700 space-y-1">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($landlord['email']); ?></p>
        <p><strong>ID:</strong> <?php echo htmlspecialchars($landlord['national_id']); ?></p>
        <p><strong>Next of Kin:</strong> <?php echo htmlspecialchars($landlord['next_of_kin']); ?> (<?php echo htmlspecialchars($landlord['kin_contact']); ?>)</p>
        <p><strong>Bank:</strong> <?php echo htmlspecialchars($landlord['bank_details']); ?></p>
        <p class="text-gray-500 text-xs">Created: <?php echo date("M d, Y", strtotime($landlord['created_at'])); ?></p>
      </div>
      <div class="mt-4 flex space-x-3">
        <a href="landlord-edit.php?id=<?php echo $landlord['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
        <a href="landlord-delete.php?id=<?php echo $landlord['id']; ?>" 
           onclick="return confirm('Are you sure you want to delete this landlord?');"
           class="text-red-600 hover:underline">Delete</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php endif; ?>

<script>
  let filtersVisible = true;

  function toggleView(view) {
    document.getElementById('tableView').classList.add('hidden');
    document.getElementById('cardView').classList.add('hidden');
    
    // Update button styles
    document.getElementById('tableBtn').classList.remove('bg-blue-600', 'text-white');
    document.getElementById('cardBtn').classList.remove('bg-blue-600', 'text-white');
    document.getElementById('tableBtn').classList.add('bg-gray-200');
    document.getElementById('cardBtn').classList.add('bg-gray-200');
    
    if (view === 'table') {
      document.getElementById('tableView').classList.remove('hidden');
      document.getElementById('tableBtn').classList.remove('bg-gray-200');
      document.getElementById('tableBtn').classList.add('bg-blue-600', 'text-white');
    } else {
      document.getElementById('cardView').classList.remove('hidden');
      document.getElementById('cardBtn').classList.remove('bg-gray-200');
      document.getElementById('cardBtn').classList.add('bg-blue-600', 'text-white');
    }
  }

  function toggleFilters() {
    const form = document.getElementById('filtersForm');
    const toggle = document.getElementById('filterToggle');
    
    if (filtersVisible) {
      form.style.display = 'none';
      toggle.textContent = 'Show Filters';
      filtersVisible = false;
    } else {
      form.style.display = 'block';
      toggle.textContent = 'Hide Filters';
      filtersVisible = true;
    }
  }

  function exportData() {
    // Get current URL parameters for filters
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    
    // Create a temporary link to download
    window.location.href = 'landlord-export.php?' + params.toString();
  }

  // Auto-submit form when selecting date or dropdown values
  document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const selectInputs = document.querySelectorAll('select');
    
    dateInputs.forEach(input => {
      input.addEventListener('change', function() {
        document.querySelector('form').submit();
      });
    });
    
    selectInputs.forEach(select => {
      select.addEventListener('change', function() {
        document.querySelector('form').submit();
      });
    });
  });

  // Set default table view
  document.addEventListener('DOMContentLoaded', function() {
    toggleView('table');
  });
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>