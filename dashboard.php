<?php
require_once 'includes/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Get user's items with more details
$user_id = $_SESSION['user_id'];

// Get user's items statistics
$stmt = $pdo->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        GROUP_CONCAT(CASE WHEN status = 'lost' THEN title END) as lost_items,
        GROUP_CONCAT(CASE WHEN status = 'found' THEN title END) as found_items,
        GROUP_CONCAT(CASE WHEN status = 'returned' THEN title END) as returned_items
    FROM items 
    WHERE user_id = ? 
    GROUP BY status
");
$stmt->execute([$user_id]);
$item_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent items (last 10)
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$recent_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all items for the map
$stmt = $pdo->prepare("SELECT * FROM items WHERE status != 'returned' ORDER BY created_at DESC");
$stmt->execute();
$all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get claims statistics
$stmt = $pdo->prepare("
    SELECT 
        c.status,
        COUNT(*) as count
    FROM claims c 
    JOIN items i ON c.item_id = i.id 
    WHERE i.user_id = ? 
    GROUP BY c.status
");
$stmt->execute([$user_id]);
$claim_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent claims on user's items
$stmt = $pdo->prepare("
    SELECT c.*, i.title, u.username as claimant_name, u.email as claimant_email
    FROM claims c 
    JOIN items i ON c.item_id = i.id 
    JOIN users u ON c.claimant_id = u.id 
    WHERE i.user_id = ? 
    ORDER BY c.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$claims_on_my_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Manage your lost and found items, track claims, and help others find their belongings.
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-500">
                    <i class="fas fa-search text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Items</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        <?php
                        // Count items where is_returned is 0
                        echo count(array_filter($user_items, function ($item) {
                            return $item['is_returned'] == 0;
                        }));
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-white dark:bg-gray-800 w-64 min-h-screen shadow-md sidebar-transition hidden md:block">
            <nav class="mt-8 px-4">
                <div class="space-y-2">
                    <a href="dashboard.php"
                        class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg border-l-4 border-blue-500">
                        <i class="fas fa-home mr-3"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="browse.php"
                        class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-search mr-3"></i>
                        <span class="font-medium">Browse Items</span>
                    </a>
                    <a href="report-item.php"
                        class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-plus mr-3"></i>
                        <span class="font-medium">Report Item</span>
                    </a>
                    <a href="map.php"
                        class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-map-marker-alt mr-3"></i>
                        <span class="font-medium">Campus Map</span>
                    </a>
                    <a href="messages.php"
                        class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-comments mr-3"></i>
                        <span class="font-medium">Messages</span>
                        <?php if (count($recent_claims) > 0): ?>
                            <span
                                class="ml-auto bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?php echo count($recent_claims); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="history.php"
                        class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-history mr-3"></i>
                        <span class="font-medium">Activity History</span>
                    </a>
                    <a href="settings.php"
                        class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-cog mr-3"></i>
                        <span class="font-medium">Settings</span>
                    </a>
                </div>

                <!-- Quick Stats Sidebar -->
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Quick Stats
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Active Items</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo $lost_count + $found_count; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending Claims</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo $pending_claims; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Return Rate</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo $total_items > 0 ? round(($returned_count / $total_items) * 100) : 0; ?>%
                            </p>
                        </div>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Returned Items</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        <?php
                        // Count items where is_returned is 1
                        echo count(array_filter($user_items, function ($item) {
                            return $item['is_returned'] == 1;
                        }));
                        ?>
                    </p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Lost Items Card -->
                <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Lost Items</p>
                            <p class="text-3xl font-semibold text-gray-900 dark:text-white mt-1">
                                <?php echo $lost_count; ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-red-100 dark:bg-red-900 text-red-500">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Items you've reported as lost
                    </div>
                </div>

                <!-- Found Items Card -->
                <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Found Items</p>
                            <p class="text-3xl font-semibold text-gray-900 dark:text-white mt-1">
                                <?php echo $found_count; ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-500">
                            <i class="fas fa-search text-xl"></i>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Items you've reported as found
                    </div>
                </div>

                <!-- Returned Items Card -->
                <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Returned Items</p>
                            <p class="text-3xl font-semibold text-gray-900 dark:text-white mt-1">
                                <?php echo $returned_count; ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-500">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Successfully returned items
                    </div>
                </div>

                <!-- Pending Claims Card -->
                <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Claims</p>
                            <p class="text-3xl font-semibold text-gray-900 dark:text-white mt-1">
                                <?php echo $pending_claims; ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-500">
                            <i class="fas fa-bell text-xl"></i>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Claims waiting for review
                    </div>
                </div>
            </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- My Items Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-semibold">My Items</h2>
                <a href="report-item.php"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center transition-colors">
                    <i class="fas fa-plus mr-1"></i> Report Item
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Item</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($user_items as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($item['image_path']): ?>
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover"
                                                    src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['title']); ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($item['title']); ?></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo htmlspecialchars($item['category']); ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    // 1. Check if the item is physically returned
                                    if ($item['is_returned'] == 1) {
                                        // It is returned! Let's see what it was originally (Lost or Found)
                                        $original_status = ucfirst($item['status']); // "Lost" or "Found"

                                        // Display: "Lost - Returned" or "Found - Returned"
                                        $status_text = $original_status . " - Returned";

                                        // Use Green for success
                                        $badge_class = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                    } else {
                                        // 2. It is NOT returned yet (Active)
                                        $status_colors = [
                                            'lost' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'found' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                        ];
                                        $badge_class = $status_colors[$item['status']];
                                        $status_text = ucfirst($item['status']); // Just "Lost" or "Found"
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badge_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo date('M j, Y', strtotime($item['date_reported'])); ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view-item.php?id=<?php echo $item['id']; ?>"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3 font-medium">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>

                                    <?php if ($item['is_returned'] == 0): ?>
                                        <a href="#"
                                            onclick="markItemReturned(<?php echo $item['id']; ?>); return false;"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 font-medium">
                                            <i class="fas fa-check-circle mr-1"></i> Mark Returned
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Campus Map Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold">Campus Map</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">View all lost and found items on campus</p>
            </div>
            <div class="p-4">
                <div id="map" class="h-96 w-full rounded-lg"></div>
            </div>
        </div>
    </div>

    <!-- Recent Claims Section -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold">Recent Claims on My Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Item</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Claimant</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Date</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($claims_on_my_items as $claim): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($claim['title']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?php echo htmlspecialchars($claim['claimant_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $claim_status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'verified' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                ];
                                ?>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $claim_status_colors[$claim['status']]; ?>">
                                    <?php echo ucfirst($claim['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?php echo date('M j, Y', strtotime($claim['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 view-claim"
                                    data-id="<?php echo $claim['id']; ?>">Review</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Report Item Modal -->
<!--
<div id="report-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">Report Lost or Found Item</h3>
                <button id="close-report-modal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="report-form" class="mt-4 space-y-4" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Name
                            *</label>
                        <input type="text" id="title" name="title" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label for="category"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category *</label>
                        <select id="category" name="category" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Select Category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Books & Notes">Books & Notes</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Keys">Keys</option>
                            <option value="ID Cards">ID Cards</option>
                            <option value="Bags">Bags</option>
                            <option value="Water Bottles">Water Bottles</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Right Column: Map & Activity -->
                <div class="space-y-8">
                    <!-- Campus Map Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold">Campus Map</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lost & Found Hotspots</p>
                        </div>
                        <div class="p-4">
                            <div id="map" class="rounded-lg"></div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <?php foreach (array_slice($campus_locations, 0, 4) as $location): ?>
                                    <div class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div
                                            class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-2">
                                            <i class="fas fa-map-marker-alt text-blue-600 dark:text-blue-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo $location['name']; ?></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <?php echo $location['items']; ?> items</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold">Recent Activity</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your latest actions</p>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="activity-item p-3 rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <div class="activity-type-icon activity-type-<?php echo $activity['type']; ?>">
                                                <i class="fas fa-<?php
                                                switch ($activity['type']) {
                                                    case 'item':
                                                        echo 'box';
                                                        break;
                                                    case 'claim':
                                                        echo 'clipboard-check';
                                                        break;
                                                    case 'message':
                                                        echo 'comment';
                                                        break;
                                                    default:
                                                        echo 'bell';
                                                }
                                                ?> text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($activity['description']); ?>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <?php echo date('M j, g:i A', strtotime($activity['date'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($recent_activity)): ?>
                                    <div class="p-4 text-center">
                                        <i class="fas fa-history text-gray-400 text-3xl mb-2"></i>
                                        <p class="text-gray-500 dark:text-gray-400">No recent activity</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-blue-100 dark:border-blue-800">
                            <h2 class="text-xl font-semibold text-blue-900 dark:text-blue-100">Quick Tips</h2>
                        </div>
                        <div class="p-4">
                            <ul class="space-y-3">
                                <li class="flex items-start space-x-2">
                                    <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                    <span class="text-sm text-blue-800 dark:text-blue-200">Report items as soon as you
                                        lose or find them</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                    <span class="text-sm text-blue-800 dark:text-blue-200">Include clear photos and
                                        detailed descriptions</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                    <span class="text-sm text-blue-800 dark:text-blue-200">Verify claimant identity
                                        before returning items</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                    <span class="text-sm text-blue-800 dark:text-blue-200">Update item status promptly
                                        after resolution</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancel-report"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> -->

<!-- Search and Filter Section -->
<div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="flex justify-between items-end mb-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Search Lost & Found Items</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div>
            <label for="search-term" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
            <input type="text" id="search-term" placeholder="Item name or description..."
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        <div>
            <label for="filter-category"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
            <select id="filter-category"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">All Categories</option>
                <option value="Electronics">Electronics</option>
                <option value="Books & Notes">Books & Notes</option>
                <option value="Clothing">Clothing</option>
                <option value="Accessories">Accessories</option>
                <option value="Keys">Keys</option>
                <option value="ID Cards">ID Cards</option>
                <option value="Bags">Bags</option>
                <option value="Water Bottles">Water Bottles</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div>
            <label for="filter-status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select id="filter-status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">All Statuses</option>
                <option value="lost">Lost</option>
                <option value="found">Found</option>
                <option value="returned">Returned</option>
            </select>
        </div>

        <div>
            <label for="filter-location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
            <select id="filter-location"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">All Locations</option>
                <option value="DKG 1">DKG 1</option>
                <option value="DKG 2">DKG 2</option>
                <option value="DKG 3">DKG 3</option>
                <option value="DKG 4">DKG 4</option>
                <option value="DKG 5">DKG 5</option>
                <option value="DKG 6">DKG 6</option>
                <option value="DKG 7">DKG 7</option>
                <option value="DKG 8">DKG 8</option>
                <option value="Laluan A">Laluan A</option>
                <option value="Laluan B">Laluan B</option>
                <option value="Laluan C">Laluan C</option>
                <option value="Laluan D">Laluan D</option>
                <option value="Main Library">Main Library</option>
                <option value="Masjid">Masjid</option>
                <option value="Pusat Sukan">Pusat Sukan</option>
                <option value="Varsity Mall">Varsity Mall</option>
            </select>
        </div>

        <div>
            <label for="filter-time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time</label>
            <select id="filter-time"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Any Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>

        <div>
            <label for="filter-sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort By</label>
            <select id="filter-sort"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="occurred_desc">Date Occurred (Recent)</option>
            </select>
        </div>
    </div>

    <div class="flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-4">
        <button id="reset-search-btn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md font-medium transition-colors">
            <i class="fas fa-undo mr-1"></i> Reset
        </button>
        <button id="search-btn" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors shadow-sm">
            <i class="fas fa-search mr-1"></i> Search Items
        </button>
    </div>

    <div id="search-results" class="space-y-4 mt-6">
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const mapItemsData = <?php echo json_encode($all_items); ?>;
</script>

<script src="js/map.js"></script>
<script src="js/app.js"></script>
<script src="js/theme.js"></script>
</body>

</html>