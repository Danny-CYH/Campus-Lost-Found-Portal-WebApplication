<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// 1. Fetch Active FOUND Items
// We want items that are 'found' AND not returned yet
$stmt = $pdo->prepare("SELECT * FROM items WHERE status = 'found' AND is_returned = 0 ORDER BY created_at DESC");
$stmt->execute();
$found_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Calculate Stats for the Header
$total_active = count($found_items);

// Count items found this week
$week_sql = "SELECT COUNT(*) FROM items WHERE status = 'found' AND is_returned = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
$week_stmt = $pdo->prepare($week_sql);
$week_stmt->execute();
$this_week_count = $week_stmt->fetchColumn();

// 3. Calculate "Return Rate" (Success Rate for Found Items)
// A. Count ALL items ever reported as found
$total_history_sql = "SELECT COUNT(*) FROM items WHERE status = 'found'";
$stmt = $pdo->prepare($total_history_sql);
$stmt->execute();
$total_found_ever = $stmt->fetchColumn();

// B. Count "Found" items that are now "Returned" to owner
$returned_sql = "SELECT COUNT(*) FROM items WHERE status = 'found' AND is_returned = 1";
$stmt = $pdo->prepare($returned_sql);
$stmt->execute();
$total_resolved = $stmt->fetchColumn();

// C. Calculate Percentage
if ($total_found_ever > 0) {
    $return_rate = round(($total_resolved / $total_found_ever) * 100);
} else {
    $return_rate = 0;
}
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Items Gallery - UUM Campus Lost & Found</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="md:hidden fixed top-4 right-4 z-50">
        <button id="mobile-menu-button"
            class="p-2 rounded-lg bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg">
            <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
        </button>
    </div>

    <div id="mobile-menu"
        class="fixed inset-0 bg-white dark:bg-gray-800 z-40 transform translate-x-full transition-transform duration-300 md:hidden">
        <div class="flex flex-col h-full p-6">
            <div class="flex justify-between items-center mb-8">
                <a href="index.php" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-search-location text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-uum-green dark:text-uum-gold">UUM Find</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found</p>
                    </div>
                </a>
                <button id="close-mobile-menu" class="p-2">
                    <i class="fas fa-times text-gray-600 dark:text-gray-400 text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-6">
                <a href="index.php" class="block text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-uum-green transition-colors py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-home mr-3"></i>Home
                </a>
                <a href="lost-items.php" class="block text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-uum-green transition-colors py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-search mr-3"></i>Lost Items
                </a>
                <a href="found-items.php" class="block text-lg font-medium text-uum-green dark:text-uum-gold py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-hand-holding-heart mr-3"></i>Found Items
                </a>
                <a href="dashboard.php" class="block text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-uum-green transition-colors py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
            </nav>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="report-item.php" class="block w-full bg-uum-green hover:bg-uum-blue text-white text-center py-3 rounded-xl font-medium transition-colors mb-3">
                        <i class="fas fa-plus-circle mr-2"></i>Report Item
                    </a>
                    <a href="auth/logout.php" class="block w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="block w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center py-3 rounded-xl font-medium transition-colors mb-3">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="auth/register.php" class="block w-full bg-uum-green hover:bg-uum-blue text-white text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-search-location text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-uum-green dark:text-uum-gold">UUM Find</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found Portal</p>
                        </div>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Home</a>
                    <a href="lost-items.php" class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Lost Items</a>
                    <a href="found-items.php" class="text-uum-green dark:text-uum-gold font-medium border-b-2 border-uum-green">Found Items</a>
                    <a href="dashboard.php" class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Dashboard</a>
                </div>

                <div class="flex items-center space-x-4">
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-110">
                        <i class="fas fa-moon text-gray-600 dark:text-uum-gold text-lg" id="theme-icon"></i>
                    </button>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="report-item.php" class="bg-uum-green hover:bg-uum-blue text-white px-4 py-2 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl hidden md:block">
                            <i class="fas fa-plus-circle mr-2"></i>Report Item
                        </a>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <div class="w-8 h-8 bg-uum-green rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
                                <span class="text-gray-700 dark:text-gray-300 hidden lg:block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <a href="dashboard.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-t-xl"><i class="fas fa-tachometer-alt mr-3"></i>Dashboard</a>
                                <a href="my-items.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"><i class="fas fa-box mr-3"></i>My Items</a>
                                <a href="update-profile.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"><i class="fas fa-user-cog mr-3"></i>Update Profile</a>
                                <a href="auth/logout.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-b-xl border-t border-gray-200 dark:border-gray-700"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex space-x-3">
                            <a href="auth/login.php" class="text-uum-green hover:text-uum-blue font-medium px-3 py-2 transition-colors hidden md:block"><i class="fas fa-sign-in-alt mr-2"></i>Login</a>
                            <a href="auth/register.php" class="bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-4 py-2 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"><i class="fas fa-user-plus mr-2"></i>Join UUM</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section class="bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-uum-green/10 text-uum-green text-sm font-medium mb-4">
                    <i class="fas fa-university mr-2"></i>Universiti Utara Malaysia
                </div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                    Found Items <span class="text-uum-green dark:text-uum-gold">Gallery</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-6">
                    Browse items found by the UUM community. If you see something that belongs to you, submit a claim.
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-uum-green dark:text-uum-gold"><?php echo $total_active; ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Active Found</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-blue-600 dark:text-blue-400"><?php echo $this_week_count; ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">This Week</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-green-600 dark:text-green-400">
                            <?php echo $return_rate; ?>%
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Return Rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-purple-600 dark:text-purple-400">24h</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Avg. Response</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-6 md:py-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-16 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                <div class="flex-1 w-full lg:max-w-md">
                    <div class="relative">
                        <input type="text" id="search-input"
                            placeholder="Search found items by name, description, or location..."
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <button id="clear-search" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="lg:hidden w-full">
                    <button id="filter-toggle" class="w-full flex items-center justify-between p-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        <span class="font-medium">Filters</span>
                        <i class="fas fa-sliders-h text-uum-green"></i>
                    </button>
                </div>

                <div id="filter-controls" class="hidden lg:flex flex-col lg:flex-row gap-3 w-full lg:w-auto">
                    <select id="category-filter" class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200">
                        <option value="">All Categories</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Books & Notes">Books & Notes</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Keys & IDs">Keys & IDs</option>
                        <option value="Bags & Wallets">Bags & Wallets</option>
                        <option value="Water Bottles">Water Bottles</option>
                        <option value="Others">Others</option>
                    </select>

                    <select id="location-filter" class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200">
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

                    <select id="date-filter" class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200">
                        <option value="">Any Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>

                    <select id="sort-filter" class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>

                    <button id="clear-filters" class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 transition-colors duration-200">
                        Clear All
                    </button>
                </div>
            </div>

            <div id="mobile-filter-panel" class="hidden lg:hidden mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select id="mobile-category-filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white">
                            <option value="">All Categories</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Books & Notes">Books & Notes</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Keys & IDs">Keys & IDs</option>
                            <option value="Bags & Wallets">Bags & Wallets</option>
                            <option value="Water Bottles">Water Bottles</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button id="mobile-apply-filters" class="flex-1 bg-uum-green hover:bg-uum-blue text-white py-2 rounded-lg font-medium transition-colors">Apply Filters</button>
                        <button id="mobile-clear-filters" class="flex-1 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 py-2 rounded-lg font-medium transition-colors">Clear</button>
                    </div>
                </div>
            </div>

            <div id="active-filters" class="hidden mt-4 flex flex-wrap gap-2"></div>
        </div>
    </section>

    <section class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 md:mb-8">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                        <span id="results-count"><?php echo $total_active; ?></span> Found Items
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1" id="results-description">
                        Showing all found items reported across UUM campus
                    </p>
                </div>

                <div class="flex items-center space-x-3 mt-4 md:mt-0">
                    <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <button class="view-toggle active p-2 rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 shadow-sm" data-view="grid"><i class="fas fa-th"></i></button>
                        <button class="view-toggle p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" data-view="list"><i class="fas fa-list"></i></button>
                    </div>
                    <select id="items-per-page" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="12">12 per page</option>
                        <option value="24">24 per page</option>
                        <option value="48">48 per page</option>
                    </select>
                </div>
            </div>

            <div id="loading-state" class="hidden flex flex-col items-center justify-center py-12">
                <div class="loading-spinner w-12 h-12 mb-4"></div>
                <p class="text-gray-600 dark:text-gray-400">Loading found items...</p>
            </div>

            <div id="empty-state" class="hidden flex flex-col items-center justify-center py-16 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-search text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No items found</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mb-6">No found items match your current filters.</p>
                <button id="reset-filters" class="bg-uum-green hover:bg-uum-blue text-white px-6 py-3 rounded-xl font-medium transition-colors">Clear All Filters</button>
            </div>

            <div id="items-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (count($found_items) > 0): ?>
                    <?php foreach ($found_items as $item): ?>
                        <div class="item-card bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group"
                            data-category="<?php echo strtolower($item['category']); ?>"
                            data-location="<?php echo strtolower($item['location_name']); ?>"
                            data-date="<?php echo $item['created_at']; ?>">

                            <div class="relative h-48 bg-gradient-to-br from-yellow-100 to-yellow-200 dark:from-yellow-900 dark:to-yellow-800">
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">Found</span>
                                </div>

                                <div class="absolute bottom-3 left-3 z-10">
                                    <?php
                                    $icon = match ($item['category']) {
                                        'Electronics' => 'fa-laptop',
                                        'Books & Notes' => 'fa-book',
                                        'Clothing' => 'fa-tshirt',
                                        'Keys & IDs' => 'fa-key',
                                        default => 'fa-box-open'
                                    };
                                    ?>
                                    <i class="fas <?php echo $icon; ?> text-3xl text-yellow-600 dark:text-yellow-400 drop-shadow-md"></i>
                                </div>

                                <?php if ($item['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity">
                                <?php endif; ?>

                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300 flex items-center justify-center">
                                    <a href="view-item.php?id=<?php echo $item['id']; ?>" class="view-item-btn opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300 bg-white text-uum-green px-4 py-2 rounded-lg font-medium shadow-lg hover:bg-gray-50">View Details</a>
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-lg truncate" title="<?php echo htmlspecialchars($item['title']); ?>"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs px-2 py-1 rounded-full whitespace-nowrap"><?php echo htmlspecialchars($item['category']); ?></span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center"><i class="fas fa-map-marker-alt mr-1"></i><span class="truncate max-w-[100px]"><?php echo htmlspecialchars($item['location_name']); ?></span></div>
                                    <div>
                                        <?php
                                        $time = strtotime($item['created_at']);
                                        $diff = time() - $time;
                                        if ($diff < 3600) echo floor($diff / 60) . " mins ago";
                                        else if ($diff < 86400) echo floor($diff / 3600) . " hours ago";
                                        else echo floor($diff / 86400) . " days ago";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No found items</h3>
                        <p class="text-gray-600 dark:text-gray-400">There are currently no reported found items.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div id="pagination" class="flex flex-col sm:flex-row items-center justify-between mt-8 md:mt-12 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 sm:mb-0">
                    Showing <span id="pagination-start">1</span> to <span id="pagination-end">12</span> of <span id="pagination-total"><?php echo $total_active; ?></span> items
                </div>
                <div class="flex space-x-1">
                    <button class="pagination-btn px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed" data-page="prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="pagination-btn active px-3 py-2 rounded-lg bg-uum-green text-white" data-page="1">1</button>
                    <button class="pagination-btn px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600" data-page="next"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 md:py-16 bg-gradient-to-r from-uum-green to-uum-blue">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
                Did you lose something?
            </h2>
            <p class="text-lg text-green-100 mb-6 max-w-2xl mx-auto">
                Check our "Found Items" list regularly. If your item isn't here, report it so others can contact you if they find it.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="report-item.php" class="bg-white text-uum-green hover:bg-gray-100 px-6 md:px-8 py-3 rounded-xl font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                        <i class="fas fa-plus-circle mr-2"></i>Report Lost Item
                    </a>
                <?php else: ?>
                    <a href="auth/register.php" class="bg-white text-uum-green hover:bg-gray-100 px-6 md:px-8 py-3 rounded-xl font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                        <i class="fas fa-user-plus mr-2"></i>Join UUM Find
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-400 text-sm">
                <p>&copy; 2025 Universiti Utara Malaysia - Lost & Found Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/theme.js"></script>
    <script src="js/mobile-menu.js"></script>

    <script src="js/lost-items_page/lost-items.js"></script>
</body>

</html>