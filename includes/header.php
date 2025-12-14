<?php
// 1. Helper Functions for Navigation
function isActivePage($pageName)
{
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_page = strtok($current_page, '?');

    $pageMap = [
        'index' => 'index.php',
        'lost-items' => 'lost-items.php',
        'found-items' => 'found-items.php',
        'my-items' => 'my-items.php',
        'update-profile' => 'update-profile.php',
        'messages' => 'messages.php',
        'report-item' => 'report-item.php',
        'view-item' => 'view-item.php',
        'dashboard' => 'dashboard.php',
        'points-history' => 'points-history.php',
        'badges' => 'badges.php',
        'leaderboard' => 'leaderboard.php'
    ];

    return isset($pageMap[$pageName]) && $current_page === $pageMap[$pageName];
}

function getActiveClass($pageName)
{
    return isActivePage($pageName) ? 'text-uum-green dark:text-uum-gold font-semibold border-b-2 border-uum-green' : '';
}

// 2. Check Login Status - Use the session that's already started
$isLoggedIn = isset($_SESSION['user_id']);

// 3. Fetch Profile Image
$userProfileImage = 'default_avatar.png'; // Default fallback

// Only query the database if the user is logged in AND $pdo exists
if ($isLoggedIn && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();

        if ($row && !empty($row['profile_image'])) {
            $userProfileImage = $row['profile_image'];
        }
    } catch (PDOException $e) {
        // Silent fail: keep using default_avatar.png if DB error occurs
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<!-- Favicon Links -->
<link rel="icon" href="icons/favicon.ico" type="image/x-icon">
<link rel="icon" href="icons/icon-32x32.png" type="image/png" sizes="32x32">
<link rel="icon" href="icons/icon-16x16.png" type="image/png" sizes="16x16">
<link rel="icon" href="icons/icon-72x72.png" type="image/png" sizes="72x72">
<link rel="apple-touch-icon" href="icons/apple-touch-icon.png">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="css/mobile-menu.css">

    <style>
        /* Mobile Navigation Styles */
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 8px;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
        }

        .mobile-nav-item:hover {
            background: linear-gradient(135deg, rgba(0, 104, 55, 0.05), rgba(0, 86, 179, 0.05));
            transform: translateY(-4px) scale(1.05);
        }

        .dark .mobile-nav-item:hover {
            background: linear-gradient(135deg, rgba(0, 104, 55, 0.1), rgba(0, 86, 179, 0.1));
        }

        .mobile-nav-item-active {
            background: linear-gradient(135deg, rgba(0, 104, 55, 0.1), rgba(0, 86, 179, 0.1));
            box-shadow: 0 4px 20px rgba(0, 104, 55, 0.15);
        }

        .dark .mobile-nav-item-active {
            background: linear-gradient(135deg, rgba(0, 104, 55, 0.2), rgba(0, 86, 179, 0.2));
            box-shadow: 0 4px 20px rgba(0, 104, 55, 0.3);
        }

        .mobile-nav-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            position: relative;
            transition: all 0.3s ease;
        }

        .mobile-nav-item:hover .mobile-nav-icon {
            transform: rotate(10deg) scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .mobile-nav-label {
            font-size: 11px;
            font-weight: 600;
            color: #4b5563;
            text-align: center;
            transition: all 0.3s ease;
        }

        .dark .mobile-nav-label {
            color: #d1d5db;
        }

        .mobile-nav-item:hover .mobile-nav-label {
            color: #006837;
            font-weight: 700;
        }

        .dark .mobile-nav-item:hover .mobile-nav-label {
            color: #FFD700;
        }

        .mobile-nav-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 10px;
            font-weight: bold;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
        }

        .dark .mobile-nav-badge {
            border-color: #1f2937;
        }

        .mobile-points-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(245, 158, 11, 0.3);
        }

        .dark .mobile-points-badge {
            border-color: #1f2937;
        }

        /* Popup Animations */
        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(0);
            }

            to {
                transform: translateY(100%);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        .mobile-menu-open {
            display: block !important;
        }

        .mobile-menu-closing {
            animation: slideDown 0.5s ease forwards;
        }

        .backdrop-open {
            animation: fadeIn 0.3s ease forwards;
        }

        .backdrop-closing {
            animation: fadeOut 0.3s ease forwards;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <!-- NAVIGATION BAR -->
    <nav class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-search-location text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-uum-green dark:text-uum-gold">UUM Find</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found Portal</p>
                        </div>
                    </a>
                </div>

                <!-- Mobile menu button (Hamburger) -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-button" type="button"
                        class="mobile-menu-btn inline-flex items-center justify-center p-3 rounded-xl text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold hover:bg-gradient-to-r hover:from-gray-100 hover:to-gray-50 dark:hover:from-gray-700 dark:hover:to-gray-800 transition-all duration-300 transform hover:scale-105">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                <!-- Desktop Menu (Unchanged) -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php"
                        class="<?php echo getActiveClass('index'); ?> text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold font-medium transition-colors px-3 py-2">
                        <div class="flex items-center space-x-2">
                            <i
                                class="fas fa-home <?php echo isActivePage('index') ? 'text-uum-green dark:text-uum-gold' : ''; ?>"></i>
                            <span>Home</span>
                        </div>
                    </a>
                    <a href="lost-items.php"
                        class="<?php echo getActiveClass('lost-items'); ?> text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold font-medium transition-colors px-3 py-2">
                        <div class="flex items-center space-x-2">
                            <i
                                class="fas fa-search <?php echo isActivePage('lost-items') ? 'text-uum-green dark:text-uum-gold' : ''; ?>"></i>
                            <span>Lost Items</span>
                        </div>
                    </a>
                    <a href="found-items.php"
                        class="<?php echo getActiveClass('found-items'); ?> text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold font-medium transition-colors px-3 py-2">
                        <div class="flex items-center space-x-2">
                            <i
                                class="fas fa-hand-holding <?php echo isActivePage('found-items') ? 'text-uum-green dark:text-uum-gold' : ''; ?>"></i>
                            <span>Found Items</span>
                        </div>
                    </a>
                    <a href="leaderboard.php"
                        class="<?php echo getActiveClass('leaderboard'); ?> text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold font-medium transition-colors px-3 py-2">
                        <div class="flex items-center space-x-2">
                            <i
                                class="fas fa-star <?php echo isActivePage('leaderboard') ? 'text-uum-green dark:text-uum-gold' : ''; ?>"></i>
                            <span>Leaderboard</span>
                        </div>
                    </a>
                </div>

                <!-- User Menu (Desktop) -->
                <div class="hidden md:flex items-center space-x-4">
                    <button id="theme-toggle"
                        class="p-2.5 rounded-xl hover:bg-gradient-to-r hover:from-gray-100 hover:to-gray-50 dark:hover:from-gray-700 dark:hover:to-gray-800 transition-all duration-300 transform hover:scale-105 hover:shadow-md">
                        <i class="fas fa-moon text-gray-600 dark:text-uum-gold text-lg transform transition-transform duration-500 hover:rotate-45"
                            id="theme-icon"></i>
                    </button>

                    <?php if ($isLoggedIn): ?>
                        <!-- Show user dropdown when logged in -->
                        <div class="relative group">
                            <button
                                class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-gradient-to-r hover:from-gray-100 hover:to-gray-50 dark:hover:from-gray-700 dark:hover:to-gray-800 transition-all duration-300 transform hover:scale-[1.02] group">
                                <div class="relative">
                                    <img src="uploads/profile_images/<?php echo htmlspecialchars($userProfileImage); ?>"
                                        alt="Profile"
                                        class="w-10 h-10 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-lg group-hover:border-uum-green dark:group-hover:border-uum-gold transition-all duration-300">
                                    <div
                                        class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white dark:border-gray-800 shadow-sm">
                                    </div>
                                </div>
                                <div class="text-left hidden lg:block">
                                    <span
                                        class="text-gray-800 dark:text-gray-200 font-medium block leading-tight group-hover:text-uum-green dark:group-hover:text-uum-gold transition-colors">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Online</span>
                                </div>
                                <i
                                    class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-xs transform transition-transform duration-300 group-hover:rotate-180"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 translate-y-2 group-hover:translate-y-0 z-50 overflow-hidden">
                                <!-- Dropdown Items -->
                                <div class="py-2">
                                    <a href="my-items.php" class="dropdown-item group/item">
                                        <div class="dropdown-icon-container">
                                            <i class="fas fa-boxes dropdown-icon"></i>
                                        </div>
                                        <div class="dropdown-content">
                                            <span class="dropdown-title">My Items</span>
                                            <span class="dropdown-subtitle">Manage your listings</span>
                                        </div>
                                        <i class="fas fa-chevron-right dropdown-arrow"></i>
                                    </a>

                                    <a href="messages.php" class="dropdown-item group/item">
                                        <div class="dropdown-icon-container">
                                            <i class="fas fa-comment-dots dropdown-icon"></i>
                                        </div>
                                        <div class="dropdown-content">
                                            <span class="dropdown-title">Messages</span>
                                            <span class="dropdown-subtitle">Chat with users</span>
                                        </div>
                                        <i class="fas fa-chevron-right dropdown-arrow"></i>
                                    </a>

                                    <a href="points-history.php" class="dropdown-item group/item">
                                        <div class="dropdown-icon-container">
                                            <i class="fas fa-star dropdown-icon"></i>
                                        </div>
                                        <div class="dropdown-content">
                                            <span class="dropdown-title">Points History</span>
                                            <span class="dropdown-subtitle">Track your rewards</span>
                                        </div>
                                        <i class="fas fa-chevron-right dropdown-arrow"></i>
                                    </a>

                                    <a href="badges.php" class="dropdown-item group/item">
                                        <div class="dropdown-icon-container">
                                            <i class="fas fa-award dropdown-icon"></i>
                                        </div>
                                        <div class="dropdown-content">
                                            <span class="dropdown-title">My Badges</span>
                                            <span class="dropdown-subtitle">Achievements earned</span>
                                        </div>
                                        <i class="fas fa-chevron-right dropdown-arrow"></i>
                                    </a>

                                    <a href="update-profile.php" class="dropdown-item group/item">
                                        <div class="dropdown-icon-container">
                                            <i class="fas fa-user-cog dropdown-icon"></i>
                                        </div>
                                        <div class="dropdown-content">
                                            <span class="dropdown-title">Profile Settings</span>
                                            <span class="dropdown-subtitle">Edit your account</span>
                                        </div>
                                        <i class="fas fa-chevron-right dropdown-arrow"></i>
                                    </a>
                                </div>

                                <!-- Logout Button -->
                                <div
                                    class="p-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                                    <a href="auth/logout.php" class="logout-button group/item">
                                        <div class="logout-icon-container">
                                            <i class="fas fa-sign-out-alt logout-icon"></i>
                                        </div>
                                        <div class="logout-content">
                                            <span class="logout-title">Logout</span>
                                            <span class="logout-subtitle">End your session</span>
                                        </div>
                                        <i class="fas fa-external-link-alt logout-arrow"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Show login/register when NOT logged in -->
                        <div class="flex items-center space-x-3">
                            <a href="auth/login.php"
                                class="text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold font-medium transition-colors px-4 py-2.5 rounded-xl hover:bg-gradient-to-r hover:from-gray-100 hover:to-gray-50 dark:hover:from-gray-700 dark:hover:to-gray-800">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </a>
                            <a href="auth/register.php"
                                class="bg-gradient-to-r from-uum-green to-uum-blue hover:from-green-700 hover:to-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                                <i class="fas fa-user-plus mr-2"></i>Register
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Popup -->
    <div id="mobile-menu" class="md:hidden fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div id="mobile-menu-backdrop"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-500"></div>

        <!-- Popup Container -->
        <div class="relative h-full w-full flex items-end justify-center">
            <!-- Popup Content -->
            <div id="mobile-menu-content"
                class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-t-3xl shadow-2xl transform transition-transform duration-500 translate-y-full">

                <!-- Handle Bar -->
                <div class="pt-4 px-4 flex justify-center">
                    <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
                </div>

                <!-- User Info Section -->
                <div class="px-6 pt-4 pb-6 border-b border-gray-100 dark:border-gray-800">
                    <?php if ($isLoggedIn): ?>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <img src="uploads/profile_images/<?php echo htmlspecialchars($userProfileImage); ?>"
                                    alt="Profile"
                                    class="w-16 h-16 rounded-2xl object-cover border-4 border-white dark:border-gray-800 shadow-xl">
                                <div
                                    class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                    <i class="fas fa-circle text-green-500 text-xs mr-2"></i>
                                    Online
                                </p>
                            </div>
                            <button id="close-mobile-menu"
                                class="p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:text-uum-green dark:hover:text-uum-gold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-gradient-to-r from-uum-green to-uum-blue flex items-center justify-center">
                                    <i class="fas fa-user text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Welcome Guest</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Login to access all features</p>
                                </div>
                            </div>
                            <button id="close-mobile-menu"
                                class="p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:text-uum-green dark:hover:text-uum-gold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Navigation Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-4 gap-4">
                        <!-- Home -->
                        <a href="index.php"
                            class="mobile-nav-item <?php echo isActivePage('index') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-green-100 to-green-50 dark:from-green-900 dark:to-green-800">
                                <i class="fas fa-home text-green-600 dark:text-green-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Home</span>
                        </a>

                        <!-- Lost Items -->
                        <a href="lost-items.php"
                            class="mobile-nav-item <?php echo isActivePage('lost-items') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800">
                                <i class="fas fa-search text-blue-600 dark:text-blue-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Lost</span>
                        </a>

                        <!-- Found Items -->
                        <a href="found-items.php"
                            class="mobile-nav-item <?php echo isActivePage('found-items') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900 dark:to-purple-800">
                                <i class="fas fa-hand-holding-heart text-purple-600 dark:text-purple-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Found</span>
                        </a>

                        <!-- Report Item -->
                        <a href="report-item.php"
                            class="mobile-nav-item <?php echo isActivePage('report-item') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-red-100 to-red-50 dark:from-red-900 dark:to-red-800">
                                <i class="fas fa-plus-circle text-red-600 dark:text-red-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Report</span>
                        </a>

                        <!-- My Items -->
                        <a href="my-items.php"
                            class="mobile-nav-item <?php echo isActivePage('my-items') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-yellow-100 to-yellow-50 dark:from-yellow-900 dark:to-yellow-800">
                                <i class="fas fa-box text-yellow-600 dark:text-yellow-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">My Items</span>
                        </a>

                        <!-- Messages -->
                        <a href="messages.php"
                            class="mobile-nav-item <?php echo isActivePage('messages') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-pink-100 to-pink-50 dark:from-pink-900 dark:to-pink-800">
                                <i class="fas fa-comments text-pink-600 dark:text-pink-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Messages</span>
                        </a>

                        <!-- Leaderboard -->
                        <a href="leaderboard.php"
                            class="mobile-nav-item <?php echo isActivePage('leaderboard') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-orange-100 to-orange-50 dark:from-orange-900 dark:to-orange-800">
                                <i class="fas fa-trophy text-orange-600 dark:text-orange-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Rankings</span>
                        </a>

                        <!-- Points History -->
                        <a href="points-history.php"
                            class="mobile-nav-item <?php echo isActivePage('points-history') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-indigo-900 dark:to-indigo-800">
                                <i class="fas fa-star text-indigo-600 dark:text-indigo-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Points</span>
                        </a>

                        <!-- Profile -->
                        <a href="update-profile.php"
                            class="mobile-nav-item <?php echo isActivePage('update-profile') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-cyan-100 to-cyan-50 dark:from-cyan-900 dark:to-cyan-800">
                                <i class="fas fa-user-cog text-cyan-600 dark:text-cyan-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Profile</span>
                        </a>

                        <!-- Badges -->
                        <a href="badges.php"
                            class="mobile-nav-item <?php echo isActivePage('badges') ? 'mobile-nav-item-active' : ''; ?>">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-rose-100 to-rose-50 dark:from-rose-900 dark:to-rose-800">
                                <i class="fas fa-award text-rose-600 dark:text-rose-400 text-xl"></i>
                            </div>
                            <span class="mobile-nav-label">Badges</span>
                        </a>

                        <!-- Theme Toggle -->
                        <button id="mobile-theme-toggle" class="mobile-nav-item">
                            <div
                                class="mobile-nav-icon bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700">
                                <i class="fas fa-palette text-gray-600 dark:text-gray-400 text-xl"
                                    id="mobile-theme-icon"></i>
                            </div>
                            <span class="mobile-nav-label">Theme</span>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-6 border-t border-gray-100 dark:border-gray-800">
                    <?php if ($isLoggedIn): ?>
                        <div class="space-y-3">
                            <a href="auth/logout.php"
                                class="flex items-center justify-center w-full px-6 py-4 bg-gradient-to-r from-red-100 to-red-200 dark:from-red-900/20 dark:to-red-800/20 text-red-600 dark:text-red-400 rounded-2xl font-semibold hover:from-red-200 hover:to-red-300 dark:hover:from-red-900/30 dark:hover:to-red-800/30 transition-all duration-300">
                                <i class="fas fa-sign-out-alt mr-3"></i>
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="auth/login.php"
                                class="flex items-center justify-center w-full px-4 py-4 bg-gradient-to-r from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700 text-gray-800 dark:text-gray-300 rounded-2xl font-semibold hover:from-gray-200 hover:to-gray-100 dark:hover:from-gray-700 dark:hover:to-gray-600 transition-all duration-300">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login
                            </a>
                            <a href="auth/register.php"
                                class="flex items-center justify-center w-full px-4 py-4 bg-gradient-to-r from-uum-green to-uum-blue text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                                <i class="fas fa-user-plus mr-2"></i>
                                Register
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-800 rounded-b-3xl">
                    <div class="text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            © <?php echo date('Y'); ?> UUM Lost & Found Portal
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/theme.js"></script>

    <script>
        // Mobile Menu Functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuContent = document.getElementById('mobile-menu-content');
        const mobileMenuBackdrop = document.getElementById('mobile-menu-backdrop');
        const closeMobileMenu = document.getElementById('close-mobile-menu');
        const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
        const mobileThemeIcon = document.getElementById('mobile-theme-icon');

        function openMobileMenu() {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('mobile-menu-open');

            // Add backdrop animation
            mobileMenuBackdrop.classList.remove('backdrop-closing');
            mobileMenuBackdrop.classList.add('backdrop-open');

            // Add content animation
            setTimeout(() => {
                mobileMenuContent.style.animation = 'slideUp 0.5s ease forwards';
                mobileMenuContent.style.transform = 'translateY(0)';
            }, 10);

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenuFunc() {
            // Add closing animations
            mobileMenuContent.style.animation = 'slideDown 0.5s ease forwards';
            mobileMenuBackdrop.classList.remove('backdrop-open');
            mobileMenuBackdrop.classList.add('backdrop-closing');

            // Wait for animation to complete before hiding
            setTimeout(() => {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('mobile-menu-open');
                document.body.style.overflow = '';
            }, 500);
        }

        // Event Listeners
        mobileMenuButton.addEventListener('click', openMobileMenu);
        closeMobileMenu.addEventListener('click', closeMobileMenuFunc);
        mobileMenuBackdrop.addEventListener('click', closeMobileMenuFunc);

        // Close mobile menu when clicking on navigation links
        document.querySelectorAll('.mobile-nav-item').forEach(item => {
            if (item.tagName === 'A') {
                item.addEventListener('click', closeMobileMenuFunc);
            }
        });

        // Mobile Theme Toggle
        mobileThemeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            // Update theme
            document.documentElement.classList.remove(currentTheme);
            document.documentElement.classList.add(newTheme);

            // Update icon
            if (newTheme === 'dark') {
                mobileThemeIcon.classList.remove('fa-moon');
                mobileThemeIcon.classList.add('fa-sun');
            } else {
                mobileThemeIcon.classList.remove('fa-sun');
                mobileThemeIcon.classList.add('fa-moon');
            }

            // Save to cookie
            document.cookie = `theme=${newTheme}; path=/; max-age=31536000; SameSite=Lax`;
        });

        // Add ripple effect to mobile menu items
        document.querySelectorAll('.mobile-nav-item').forEach(item => {
            item.addEventListener('click', function (e) {
                if (this.tagName === 'BUTTON' || this.tagName === 'A') {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(0, 104, 55, 0.1);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        top: ${y}px;
                        left: ${x}px;
                        pointer-events: none;
                    `;

                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                }
            });
        });

        // Initialize mobile theme icon
        document.addEventListener('DOMContentLoaded', () => {
            const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            if (mobileThemeIcon) {
                if (currentTheme === 'dark') {
                    mobileThemeIcon.classList.remove('fa-moon');
                    mobileThemeIcon.classList.add('fa-sun');
                } else {
                    mobileThemeIcon.classList.remove('fa-sun');
                    mobileThemeIcon.classList.add('fa-moon');
                }
            }
        });
    </script>
</body>

</html>