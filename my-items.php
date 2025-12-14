<?php
require_once 'includes/config.php';
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
}
if (file_exists('includes/points_system.php')) {
    require_once 'includes/points_system.php';
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

    // 4. Get user stats from points system
    $userStats = $pointsSystem->getUserStats($user_id);

    // 5. Get leaderboard data
    $leaderboard = $pointsSystem->getLeaderboard(5);

    // 6. Get point history
    $pointHistory = $pointsSystem->getPointHistory($user_id, 5);

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
    <style>
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #e5e7eb;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.5s ease-in-out;
        }

        .level-indicator {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .badge-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .badge-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 8px;
        }

        .dashboard-nav a {
            transition: all 0.3s ease;
        }

        .dashboard-nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .dashboard-nav a.active {
            background-color: rgba(255, 255, 255, 0.25);
            font-weight: 600;
        }
    </style>
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
    <section class="bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 py-6 md:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        My <span class="text-uum-green dark:text-uum-gold">Dashboard</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Track your items, points, and achievements in one place.
                    </p>
                </div>

                <a href="report-item.php"
                    class="inline-flex items-center bg-uum-green hover:bg-uum-blue text-white px-5 py-3 rounded-xl font-medium shadow-lg transition-all transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i> Report New Item
                </a>
            </div>
        </div>
    </section>

    <!-- Dashboard Navigation -->
    <section class="py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dashboard-nav">
                <div class="flex flex-wrap justify-center gap-2">
                    <a href="#items-section" class="px-4 py-2 rounded-lg text-white flex items-center">
                        <i class="fas fa-box mr-2"></i> My Items
                    </a>
                    <a href="#points-section" class="px-4 py-2 rounded-lg text-white flex items-center">
                        <i class="fas fa-star mr-2"></i> Points & Badges
                    </a>
                    <a href="#leaderboard-section" class="px-4 py-2 rounded-lg text-white flex items-center">
                        <i class="fas fa-trophy mr-2"></i> Leaderboard
                    </a>
                    <a href="leaderboard.php" class="px-4 py-2 rounded-lg text-white flex items-center">
                        <i class="fas fa-chart-line mr-2"></i> Full Leaderboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Points & Stats -->
            <div class="lg:col-span-1 space-y-6" id="points-section">
                <!-- Points Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 stat-card">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Points</h2>
                        <div class="level-indicator">
                            Lv. <?php echo $userStats['level']; ?>
                        </div>
                    </div>

                    <div class="text-center mb-6">
                        <div class="text-5xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                            <?php echo number_format($userStats['points']); ?>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">Total Points</p>
                    </div>

                    <!-- Progress to next level -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Level <?php echo $userStats['level']; ?></span>
                            <span>Level <?php echo $userStats['level'] + 1; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo ($userStats['points'] % 100); ?>%">
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-500 dark:text-gray-500 mt-1">
                            <?php echo 100 - ($userStats['points'] % 100); ?> points to next level
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                <?php echo $userStats['total_transactions']; ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Actions</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                <?php echo count($userStats['badges']); ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Badges</div>
                        </div>
                    </div>
                </div>

                <!-- Badges Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">My Badges</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <?php if (!empty($userStats['badges'])): ?>
                            <?php foreach ($userStats['badges'] as $badge): ?>
                                <div
                                    class="text-center badge-card p-3 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-lg">
                                    <div class="text-2xl text-blue-600 dark:text-blue-400 mb-2">
                                        <i class="<?php echo $badge['icon']; ?>"></i>
                                    </div>
                                    <div class="font-semibold text-gray-800 dark:text-white text-sm">
                                        <?php echo $badge['name']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-3 text-center py-4">
                                <i class="fas fa-award text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                                <p class="text-gray-500 dark:text-gray-400">No badges yet. Start helping to earn badges!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="badges.php"
                        class="block text-center mt-4 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                        View All Badges →
                    </a>
                </div>

                <!-- Points History Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Points</h2>

                    <?php if (count($pointHistory) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($pointHistory as $transaction): ?>
                                <div
                                    class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $transaction['transaction_type'] == 'earned' ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300'; ?>">
                                            <i
                                                class="fas fa-<?php echo $transaction['transaction_type'] == 'earned' ? 'plus' : 'minus'; ?> text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo ucfirst(str_replace('_', ' ', $transaction['source'])); ?>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d', strtotime($transaction['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="font-bold <?php echo $transaction['transaction_type'] == 'earned' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                        <?php echo $transaction['transaction_type'] == 'earned' ? '+' : '-'; ?>
                                        <?php echo $transaction['points']; ?> pts
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="points-history.php"
                            class="block text-center mt-4 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                            View Full History →
                        </a>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                            <p class="text-gray-500 dark:text-gray-400">No points activity yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Items & Leaderboard -->
            <div class="lg:col-span-2 space-y-6">
                <!-- My Items Section -->
                <div class="space-y-6" id="items-section">
                    <!-- TABLE 1: ACTIVE LISTINGS -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <i class="fas fa-list-ul mr-2 text-blue-500"></i> Active Listings
                        </h2>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <?php if (!empty($active_items)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                                            <tr>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Item Details</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Status</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Date Posted</th>
                                                <th
                                                    class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Actions</th>
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
                                                            <div
                                                                class="flex-shrink-0 h-12 w-12 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 relative">
                                                                <?php if (!empty($item['image_path'])): ?>
                                                                    <img class="h-12 w-12 object-cover"
                                                                        src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                                                        alt="Item Image">
                                                                <?php else: ?>
                                                                    <div
                                                                        class="h-full w-full flex items-center justify-center text-gray-400">
                                                                        <i class="fas fa-image"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="ml-3">
                                                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                                </div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                    <?php echo htmlspecialchars($item['category']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <!-- Column 2 -->
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php if ($item['status'] == 'lost'): ?>
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-200 dark:border-red-800">Lost</span>
                                                        <?php else: ?>
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800">Found</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <!-- Column 3 -->
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                                    </td>
                                                    <!-- Column 4: FULL ACTIONS -->
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <div class="flex items-center justify-center space-x-2">
                                                            <a href="view-myitem.php?id=<?php echo $item['id']; ?>"
                                                                class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                                title="View"><i class="fas fa-eye"></i></a>

                                                            <a href="edit-item.php?id=<?php echo $item['id']; ?>"
                                                                class="p-1.5 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 rounded-lg transition-colors"
                                                                title="Edit"><i class="fas fa-edit"></i></a>

                                                            <button onclick="markItemReturned(<?php echo $item['id']; ?>)"
                                                                class="p-1.5 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                                                                title="Mark as Returned"><i
                                                                    class="fas fa-check-circle"></i></button>

                                                            <button onclick="deleteItem(<?php echo $item['id']; ?>)"
                                                                class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                                                title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-12">
                                    <div
                                        class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-folder-open text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No active items</h3>
                                    <p class="text-gray-500 dark:text-gray-400 mt-1">You don't have any open reports.</p>
                                    <a href="report-item.php"
                                        class="mt-4 inline-block bg-uum-green hover:bg-uum-blue text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        Report First Item
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- TABLE 2: RETURNED / HISTORY -->
                    <?php if (!empty($returned_items)): ?>
                        <div class="opacity-80">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="fas fa-history mr-2 text-green-600"></i> History (Returned)
                            </h2>

                            <div
                                class="bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-inner border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-100 dark:bg-gray-900">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                                    Item</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                                    Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                                    Date</th>
                                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">
                                                    View</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <?php foreach ($returned_items as $item): ?>
                                                <tr class="bg-gray-50 dark:bg-gray-800/50 text-gray-500">
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-lg overflow-hidden grayscale">
                                                                <?php if (!empty($item['image_path'])): ?>
                                                                    <img class="h-8 w-8 object-cover"
                                                                        src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                                                        alt="">
                                                                <?php else: ?>
                                                                    <div
                                                                        class="h-full w-full flex items-center justify-center text-gray-400">
                                                                        <i class="fas fa-image"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="ml-3">
                                                                <div
                                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                            Returned
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-3 text-sm text-gray-500">
                                                        <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                                    </td>
                                                    <td class="px-6 py-3 text-center">
                                                        <a href="view-myitem.php?id=<?php echo $item['id']; ?>"
                                                            class="text-blue-400 hover:text-blue-600 transition-colors"
                                                            title="View History">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
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

                <!-- Leaderboard Section -->
                <div id="leaderboard-section">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Top Helpers</h2>
                            <a href="leaderboard.php"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                View Full →
                            </a>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($leaderboard as $index => $user): ?>
                                <div
                                    class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg <?php echo $user['id'] == $_SESSION['user_id'] ? 'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800' : ''; ?>">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 font-bold">
                                            <?php echo $index + 1; ?>
                                        </div>
                                        <div>
                                            <div
                                                class="font-medium <?php echo $user['id'] == $_SESSION['user_id'] ? 'text-blue-700 dark:text-blue-300' : 'text-gray-900 dark:text-white'; ?>">
                                                <?php echo htmlspecialchars($user['username']); ?>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span
                                                        class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-1 rounded ml-2">You</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Level
                                                <?php echo $user['level']; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        <?php echo number_format($user['points']); ?> pts
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="../js/theme.js"></script>
    <script src="../js/app.js"></script>
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
            console.log('Marking item as returned:', id); // Debug log

            if (confirm('Mark this item as Returned?\n\nThis will close the case and award points if this is a found item.')) {
                const url = 'api/items.php?action=mark_returned';
                console.log('URL:', url); // Debug log

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `item_id=${id}`
                }).then(res => {
                    console.log('Response status:', res.status); // Debug log
                    return res.json();
                }).then(data => {
                    console.log('Response data:', data); // Debug log
                    if (data.success) {
                        showNotification('success', data.message);
                        setTimeout(() => window.location.reload(), 2000);
                        if (data.new_badges) {
                            setTimeout(() => showBadgeNotification(data.new_badges), 500);
                        }
                    } else {
                        showNotification('error', data.message);
                    }
                }).catch(error => {
                    console.error('Fetch error:', error); // Debug log
                    showNotification('error', 'An error occurred. Please try again.');
                });
            }
        }

        // Add these notification functions at the bottom of your script
        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-md ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
            notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
            <div>${message}</div>
            <button class="ml-4" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        function showBadgeNotification(badges) {
            badges.forEach(badge => {
                const badgeNotification = document.createElement('div');
                badgeNotification.className = 'fixed top-20 right-4 p-6 rounded-lg shadow-xl z-50 max-w-md bg-gradient-to-r from-yellow-400 to-orange-500 text-white dark:from-yellow-600 dark:to-orange-700';
                badgeNotification.innerHTML = `
            <div class="flex items-center">
                <div class="text-3xl mr-4">
                    <i class="${badge.badge_icon}"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">New Badge Unlocked!</h3>
                    <p class="font-semibold">${badge.badge_name}</p>
                    <p class="text-sm opacity-90">${badge.description}</p>
                    <p class="text-xs mt-1">Earned on: ${new Date().toLocaleDateString()}</p>
                </div>
                <button class="ml-4" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
                document.body.appendChild(badgeNotification);

                setTimeout(() => {
                    if (badgeNotification.parentNode) {
                        badgeNotification.remove();
                    }
                }, 8000);
            });
        }

        // Smooth scroll for dashboard navigation
        document.querySelectorAll('.dashboard-nav a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId.startsWith('#')) {
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                } else {
                    window.location.href = targetId;
                }
            });
        });
    </script>
</body>

</html>