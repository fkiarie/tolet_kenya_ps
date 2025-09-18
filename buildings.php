<?php
require 'auth/auth_check.php';
require 'config/db.php';

// Get current agent
$agent_id = $_SESSION['agent_id'];

if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

// Search and filter functionality
$search = $_GET['search'] ?? '';
$county_filter = $_GET['county'] ?? '';

// Build the query with search and filters
$sql = "
  SELECT 
    b.*, 
    l.name AS landlord_name,
    l.phone AS landlord_phone,
    l.email AS landlord_email,
    COUNT(DISTINCT u.id) as unit_count,
    COUNT(DISTINCT CASE WHEN u.status = 'vacant' THEN u.id END) as vacant_units,
    COUNT(DISTINCT CASE WHEN u.status = 'occupied' THEN u.id END) as occupied_units
  FROM buildings b
  JOIN landlords l ON b.landlord_id = l.id
  LEFT JOIN units u ON b.id = u.building_id
  WHERE l.agent_id = :agent_id
";

$params = ['agent_id' => $agent_id];

if (!empty($search)) {
    $sql .= " AND (b.name LIKE :search OR l.name LIKE :search2)";
    $params['search'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
}

if (!empty($county_filter)) {
    $sql .= " AND b.county = :county";
    $params['county'] = $county_filter;
}

$sql .= " GROUP BY b.id ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique counties for filter dropdown
$county_stmt = $conn->prepare("
  SELECT DISTINCT county 
  FROM buildings b
  JOIN landlords l ON b.landlord_id = l.id
  WHERE l.agent_id = :agent_id
  ORDER BY county
");
$county_stmt->execute(['agent_id' => $agent_id]);
$counties = $county_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buildings - Tolet Kenya</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

<?php include 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-6">
  <!-- Header Section -->
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Buildings Management</h1>
      <p class="text-gray-600">Manage all your buildings and properties</p>
    </div>
    <a href="building-add.php" 
       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2 shadow-md">
      <i class="fas fa-plus"></i>
      <span>Add New Building</span>
    </a>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-600 text-sm">Total Buildings</p>
          <p class="text-2xl font-bold text-blue-600"><?php echo count($buildings); ?></p>
        </div>
        <i class="fas fa-building text-blue-500 text-2xl"></i>
      </div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-600 text-sm">Total Units</p>
          <p class="text-2xl font-bold text-green-600"><?php echo array_sum(array_column($buildings, 'unit_count')); ?></p>
        </div>
        <i class="fas fa-home text-green-500 text-2xl"></i>
      </div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-600 text-sm">Vacant Units</p>
          <p class="text-2xl font-bold text-yellow-600"><?php echo array_sum(array_column($buildings, 'vacant_units')); ?></p>
        </div>
        <i class="fas fa-key text-yellow-500 text-2xl"></i>
      </div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-600 text-sm">Occupied Units</p>
          <p class="text-2xl font-bold text-red-600"><?php echo array_sum(array_column($buildings, 'occupied_units')); ?></p>
        </div>
        <i class="fas fa-users text-red-500 text-2xl"></i>
      </div>
    </div>
  </div>

  <!-- Search and Filter Section -->
  <div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-4">
      <div class="flex-1">
        <input type="text" 
               name="search" 
               value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Search buildings or landlords..." 
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
      </div>
      <div class="md:w-48">
        <select name="county" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <option value="">All Counties</option>
          <?php foreach ($counties as $county): ?>
            <option value="<?php echo htmlspecialchars($county); ?>" 
                    <?php echo $county_filter === $county ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($county); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex gap-2">
        <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
          <i class="fas fa-search mr-2"></i>Search
        </button>
        <a href="buildings.php" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
          <i class="fas fa-times mr-2"></i>Clear
        </a>
      </div>
    </form>
  </div>

  <!-- Success Message -->
  <?php if (isset($_GET['success'])): ?>
    <div id="success-alert" 
         class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg flex justify-between items-center">
      <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>
          <?php echo $_GET['success'] === 'added' ? 'Building added successfully!' : 
                    ($_GET['success'] === 'updated' ? 'Building updated successfully!' : 'Building deleted successfully!'); ?>
        </span>
      </div>
      <button onclick="document.getElementById('success-alert').style.display='none';" 
              class="text-green-700 hover:text-green-900 font-bold">
        <i class="fas fa-times"></i>
      </button>
    </div>
  <?php endif; ?>

  <!-- Buildings Table -->
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <?php if (empty($buildings)): ?>
      <div class="p-8 text-center">
        <i class="fas fa-building text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Buildings Found</h3>
        <p class="text-gray-600 mb-4">
          <?php echo !empty($search) || !empty($county_filter) ? 'No buildings match your search criteria.' : 'You haven\'t added any buildings yet.'; ?>
        </p>
        <?php if (empty($search) && empty($county_filter)): ?>
          <a href="building-add.php" 
             class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200 inline-flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Add Your First Building</span>
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Building Info</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Landlord</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Units</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($buildings as $building): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-blue-600"></i>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($building['name']); ?>
                      </div>
                      <div class="text-sm text-gray-500">
                        Added <?php echo date('M j, Y', strtotime($building['created_at'])); ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                    <?php echo htmlspecialchars($building['county']); ?>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900"><?php echo htmlspecialchars($building['landlord_name']); ?></div>
                  <?php if ($building['landlord_phone']): ?>
                    <div class="text-sm text-gray-500">
                      <i class="fas fa-phone text-gray-400 mr-1"></i>
                      <?php echo htmlspecialchars($building['landlord_phone']); ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      <?php echo $building['unit_count']; ?> Total
                    </span>
                    <?php if ($building['vacant_units'] > 0): ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <?php echo $building['vacant_units']; ?> Vacant
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php
                  $occupancy_rate = $building['unit_count'] > 0 ? ($building['occupied_units'] / $building['unit_count']) * 100 : 0;
                  $status_color = $occupancy_rate >= 80 ? 'green' : ($occupancy_rate >= 50 ? 'yellow' : 'red');
                  ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $status_color; ?>-100 text-<?php echo $status_color; ?>-800">
                    <?php echo round($occupancy_rate); ?>% Occupied
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                  <a href="units.php?building_id=<?php echo $building['id']; ?>" 
                     class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" 
                     title="View Units">
                    <i class="fas fa-home"></i>
                  </a>
                  <a href="building-edit.php?id=<?php echo $building['id']; ?>" 
                     class="text-blue-600 hover:text-blue-900 transition-colors duration-200" 
                     title="Edit Building">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="building-delete.php?id=<?php echo $building['id']; ?>" 
                     onclick="return confirm('Are you sure you want to delete this building? This will also delete all associated units.');"
                     class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                     title="Delete Building">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>