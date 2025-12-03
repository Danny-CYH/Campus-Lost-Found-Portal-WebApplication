<?php
// 1. ENABLE ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php';
// Include functions if available
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
}

// Start session if not started (needed for the Login/Dashboard button logic)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize arrays
$recent_found_items = [];
$recent_lost_items = [];
$items_returned_count = 0;
$top_display_items = [];

try {
    if (isset($pdo)) {
        // A. Fetch 3 Recent FOUND Items (Active Only)
        $stmt = $pdo->prepare("SELECT * FROM items WHERE status = 'found' AND is_returned = 0 ORDER BY created_at DESC LIMIT 3");
        $stmt->execute();
        $recent_found_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // B. Fetch 3 Recent LOST Items (Active Only)
        $stmt = $pdo->prepare("SELECT * FROM items WHERE status = 'lost' AND is_returned = 0 ORDER BY created_at DESC LIMIT 3");
        $stmt->execute();
        $recent_lost_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // C. Fetch Total Returned Count (For Stats)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE is_returned = 1");
        $stmt->execute();
        $items_returned_count = $stmt->fetchColumn();

        // D. Prepare Top 3 Combined List for the "Recent Items" Grid
        $all_recent = array_merge($recent_found_items, $recent_lost_items);
        usort($all_recent, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        $top_display_items = array_slice($all_recent, 0, 3);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UUM Campus Lost & Found Portal - Find Your Belongings</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        },
                        uum: {
                            green: '#006837',
                            gold: '#FFD700',
                            blue: '#0056b3'
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 py-12 md:py-20">
        <div class="absolute inset-0 bg-white/20 dark:bg-gray-800/20"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-uum-green/10 text-uum-green text-sm font-medium mb-6">
                        <i class="fas fa-university mr-2"></i>Universiti Utara Malaysia
                    </div>
                    <h1 class="text-3xl md:text-4xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 leading-tight">
                        Lost Something in
                        <span class="text-uum-green dark:text-uum-gold">UUM Campus?</span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-6 md:mb-8 max-w-2xl">
                        UUM's official lost and found portal helps students and staff reunite with their belongings across our beautiful campus.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center lg:justify-start">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Report Item Button (Desktop) -->
                            <a href="report-item.php" class="bg-green-600 hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                                <i class="fas fa-plus-circle mr-2"></i>Report Item
                            </a>
                        <?php else: ?>
                            <a href="auth/login.php" class="bg-green-600 hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login to Report
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="mt-8 md:mt-12 grid grid-cols-3 gap-4 md:gap-8 text-center">
                        <div>
                            <div class="text-2xl md:text-3xl font-bold text-uum-green dark:text-uum-gold"><?php echo $items_returned_count; ?>+</div>
                            <div class="text-sm md:text-base text-gray-600 dark:text-gray-400">Items Returned</div>
                        </div>
                        <div>
                            <div class="text-2xl md:text-3xl font-bold text-green-600 dark:text-green-400">89%</div>
                            <div class="text-sm md:text-base text-gray-600 dark:text-gray-400">Return Rate</div>
                        </div>
                        <div>
                            <div class="text-2xl md:text-3xl font-bold text-blue-600 dark:text-blue-400">24/7</div>
                            <div class="text-sm md:text-base text-gray-600 dark:text-gray-400">Active Support</div>
                        </div>
                    </div>
                </div>

                <div class="relative hidden lg:block">
                    <div class="relative z-10 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-4 md:p-6 transform rotate-1 hover:rotate-0 transition-transform duration-500">
                        <div class="bg-gradient-to-r from-uum-green to-uum-blue text-white p-3 md:p-4 rounded-xl mb-4 md:mb-6">
                            <h3 class="text-base md:text-lg font-semibold">Live Campus Updates</h3>
                            <p class="text-green-100 text-sm">Real-time lost & found feed</p>
                        </div>

                        <div class="space-y-3 md:space-y-4">
                            <!-- Dynamic Mini Feed (Top 3 recent mixed) -->
                            <?php if (!empty($top_display_items)): ?>
                                <?php foreach ($top_display_items as $feed_item):
                                    $isFound = ($feed_item['status'] === 'found');
                                    $iconClass = $isFound ? 'fa-check-circle text-green-600 dark:text-green-400' : 'fa-exclamation-circle text-red-600 dark:text-red-400';
                                    $bgClass = $isFound ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900';
                                ?>
                                    <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="w-10 h-10 md:w-12 md:h-12 <?php echo $bgClass; ?> rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas <?php echo $iconClass; ?> text-lg"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm md:text-base truncate">
                                                <?php echo htmlspecialchars($feed_item['title']); ?>
                                            </h4>
                                            <p class="text-xs md:text-sm text-gray-600 dark:text-gray-400 truncate">
                                                <?php echo ucfirst($feed_item['status']); ?> at <?php echo htmlspecialchars($feed_item['location_name']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-4 text-center text-gray-500">No recent activity.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Floating elements -->
                    <div class="absolute -top-4 -left-4 w-16 h-16 md:w-24 md:h-24 bg-uum-green/20 dark:bg-uum-green/30 rounded-full opacity-50 animate-float hidden md:block"></div>
                    <div class="absolute -bottom-4 -right-4 w-14 h-14 md:w-20 md:h-20 bg-uum-blue/20 dark:bg-uum-blue/30 rounded-full opacity-50 animate-float hidden md:block" style="animation-delay: 2s;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Items Section (REAL DATA) -->
    <section id="recent-items" class="py-12 md:py-20 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">
                    Recently Reported at <span class="text-uum-green dark:text-uum-gold">UUM</span>
                </h2>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    The latest active reports from our community.
                </p>
            </div>

            <!-- Grid: Limit to 3 Real Items -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <?php if (!empty($top_display_items)): ?>
                    <?php foreach ($top_display_items as $item):
                        // Determine status/colors
                        $isFound = ($item['status'] === 'found');
                        $statusText = $isFound ? 'Found' : 'Lost';
                        $badgeClass = $isFound ? 'bg-green-500 text-white' : 'bg-red-500 text-white';

                        $bgClass = $isFound
                            ? 'bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800'
                            : 'bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800';

                        $iconClass = $isFound ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                    ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group relative border border-gray-100 dark:border-gray-700 h-full flex flex-col">

                            <!-- Image / Banner -->
                            <div class="relative h-48 <?php echo $bgClass; ?> shrink-0">
                                <div class="absolute top-4 right-4 z-10">
                                    <span class="<?php echo $badgeClass; ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide shadow-sm">
                                        <?php echo $statusText; ?>
                                    </span>
                                </div>

                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($item['title']); ?>"
                                        class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity">
                                <?php else: ?>
                                    <div class="absolute bottom-4 left-4">
                                        <i class="fas fa-box-open text-4xl <?php echo $iconClass; ?> drop-shadow-md"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Full Card Link (Clickable for Everyone) -->
                                <a href="view-item.php?id=<?php echo $item['id']; ?>" class="absolute inset-0 z-20 focus:outline-none"></a>
                            </div>

                            <!-- Content -->
                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 truncate">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm line-clamp-2 flex-1">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mt-auto pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center truncate pr-2">
                                        <i class="fas fa-map-marker-alt mr-2 text-uum-green flex-shrink-0"></i>
                                        <span class="truncate"><?php echo htmlspecialchars($item['location_name']); ?></span>
                                    </div>
                                    <div class="flex-shrink-0 text-xs">
                                        <?php
                                        $time = strtotime($item['created_at']);
                                        $diff = time() - $time;
                                        if ($diff < 3600) echo floor($diff / 60) . "m ago";
                                        else if ($diff < 86400) echo floor($diff / 3600) . "h ago";
                                        else echo floor($diff / 86400) . "d ago";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="col-span-full py-16 text-center bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">Quiet Day on Campus</h3>
                        <p class="text-gray-600 dark:text-gray-400">No items have been reported recently.</p>
                    </div>
                <?php endif; ?>

            </div>

            <!-- View More Button -->
            <div class="text-center mt-12">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="lost-items.php" class="inline-flex items-center bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-8 py-3 rounded-xl font-semibold text-lg transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                        <i class="fas fa-th-large mr-2"></i> Browse All Items
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="inline-flex items-center bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-8 py-3 rounded-xl font-semibold text-lg transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to View More
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section (Kept Original) -->
    <section id="features" class="py-12 md:py-20 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Why Use UUM Find?</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl text-center">
                    <div class="w-16 h-16 mx-auto bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-shield-alt text-2xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Secure & Verified</h3>
                    <p class="text-gray-600 dark:text-gray-400">Only verified UUM students and staff can post and claim items.</p>
                </div>
                <!-- Feature 2 -->
                <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl text-center">
                    <div class="w-16 h-16 mx-auto bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-map-marked-alt text-2xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Campus Map Integration</h3>
                    <p class="text-gray-600 dark:text-gray-400">Pinpoint exact locations like DKG, Library, or Cafeteria.</p>
                </div>
                <!-- Feature 3 -->
                <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl text-center">
                    <div class="w-16 h-16 mx-auto bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-bell text-2xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Instant Alerts</h3>
                    <p class="text-gray-600 dark:text-gray-400">Get notified immediately when someone finds your lost item.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="js/theme.js"></script>
    <script src="js/mobile-menu.js"></script>
</body>

</html>