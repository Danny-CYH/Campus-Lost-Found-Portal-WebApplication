<?php
require_once 'includes/config.php';
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
}

// 1. Security Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User's Items
try {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. SEPARATE ITEMS INTO TWO LISTS
    $active_items = [];
    $returned_items = [];

    foreach ($all_items as $item) {
        // Safe check for is_returned
        $is_returned = isset($item['is_returned']) ? $item['is_returned'] : 0;

        if ($is_returned == 1) {
            $returned_items[] = $item;
        } else {
            $active_items[] = $item;
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        uum: {
                            green: '#006837',
                            gold: '#FFD700',
                            blue: '#0056b3'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- Header Section -->
    <section class="bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 py-8 md:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        My Reported <span class="text-uum-green dark:text-uum-gold">Items</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Manage your active listings and view your history.
                    </p>
                </div>

                <a href="report-item.php" class="inline-flex items-center bg-uum-green hover:bg-uum-blue text-white px-5 py-3 rounded-xl font-medium shadow-lg transition-all transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i> Report New Item
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <!-- TABLE 1: ACTIVE LISTINGS -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                    <i class="fas fa-list-ul mr-2 text-blue-500"></i> Active Listings
                </h2>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <?php if (!empty($active_items)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item Details</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date Posted</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($active_items as $item): ?>
                                        <?php
                                        $safe_item_json = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                                            <!-- Column 1 -->
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 relative">
                                                        <?php if (!empty($item['image_path'])): ?>
                                                            <img class="h-16 w-16 object-cover" src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Item Image">
                                                        <?php else: ?>
                                                            <div class="h-full w-full flex items-center justify-center text-gray-400"><i class="fas fa-image text-2xl"></i></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($item['title']); ?></div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            <?php echo htmlspecialchars($item['category']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Column 2 -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($item['status'] == 'lost'): ?>
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-200 dark:border-red-800">Lost</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800">Found</span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- Column 3 -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                            </td>
                                            <!-- Column 4: FULL ACTIONS -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <a href="view-myitem.php?id=<?php echo $item['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View"><i class="fas fa-eye text-lg"></i></a>

                                                    <a href="edit-item.php?id=<?php echo $item['id']; ?>" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit"><i class="fas fa-edit text-lg"></i></a>

                                                    <button onclick="markItemReturned(<?php echo $item['id']; ?>)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Mark as Returned"><i class="fas fa-check-circle text-lg"></i></button>

                                                    <button onclick="deleteItem(<?php echo $item['id']; ?>)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><i class="fas fa-trash-alt text-lg"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-16">
                            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-folder-open text-3xl text-gray-400"></i></div>
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white">No active items</h3>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">You don't have any open reports.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TABLE 2: RETURNED / HISTORY (Read Only) -->
            <?php if (!empty($returned_items)): ?>
                <div class="opacity-80">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <i class="fas fa-history mr-2 text-green-600"></i> History (Returned)
                    </h2>

                    <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-inner border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Original Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Date Resolved</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($returned_items as $item): ?>
                                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-gray-500">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-lg overflow-hidden grayscale">
                                                        <?php if (!empty($item['image_path'])): ?>
                                                            <img class="h-10 w-10 object-cover" src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="">
                                                        <?php else: ?>
                                                            <div class="h-full w-full flex items-center justify-center text-gray-400"><i class="fas fa-image"></i></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($item['title']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                    <?php echo ucfirst($item['status']); ?> - Returned
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <!-- ONLY VIEW BUTTON AVAILABLE -->
                                                <a href="view-myitem.php?id=<?php echo $item['id']; ?>" class="text-blue-400 hover:text-blue-600 transition-colors" title="View History">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <!-- No Edit, No Delete, No Return buttons here -->
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="js/theme.js"></script>
    <script src="js/app.js"></script>
    <script>
        function deleteItem(id) {
            if (confirm('Are you sure you want to PERMANENTLY delete this item?')) {
                fetch('api/items.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function markItemReturned(id) {
            if (confirm('Mark this item as Returned? This will close the case and move it to history.')) {
                fetch('api/items.php?action=mark_returned', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `item_id=${id}`
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>

</html>