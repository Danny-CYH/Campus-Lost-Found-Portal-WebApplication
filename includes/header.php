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
        'view-item' => 'view-item.php'
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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/mobile-menu.css">
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
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-colors">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                <!-- Desktop Menu -->
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
                </div>

                <!-- User Menu (Desktop) -->
                <div class="hidden md:flex items-center space-x-4">
                    <button id="theme-toggle"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300">
                        <i class="fas fa-moon text-gray-600 dark:text-uum-gold text-lg" id="theme-icon"></i>
                    </button>

                    <?php if ($isLoggedIn): ?>
                        <!-- Show user dropdown when logged in -->
                        <div class="relative group">
                            <button
                                class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <img src="uploads/profile_images/<?php echo htmlspecialchars($userProfileImage); ?>"
                                    alt="Profile"
                                    class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm">
                                <span
                                    class="text-gray-700 dark:text-gray-300 hidden lg:block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <a href="my-items.php"
                                    class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <i class="fas fa-box mr-3"></i>My Items
                                </a>
                                <a href="update-profile.php"
                                    class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <i class="fas fa-user-cog mr-3"></i>Profile
                                </a>
                                <a href="messages.php"
                                    class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <i class="fas fa-comments mr-3"></i>Messages
                                </a>
                                <a href="auth/logout.php"
                                    class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-b-xl border-t border-gray-200 dark:border-gray-700">
                                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Show login/register when NOT logged in -->
                        <div class="flex items-center space-x-3">
                            <a href="auth/login.php"
                                class="text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold font-medium transition-colors">
                                Login
                            </a>
                            <a href="auth/register.php"
                                class="bg-uum-green hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Register
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Popup Modal) - FIXED POSITION -->
        <div id="mobile-menu" class="md:hidden hidden">
            <!-- Overlay -->
            <div id="mobile-menu-overlay"
                class="fixed inset-0 bg-black/50 z-40 transition-opacity duration-300 opacity-0"></div>

            <!-- Modal Container - CENTERED -->
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Modal Content - WITH PROPER MARGINS -->
                <div
                    class="relative w-full max-w-md max-h-[85vh] overflow-y-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl mx-auto my-auto">
                    <!-- Close Button -->
                    <button id="close-mobile-menu" type="button"
                        class="absolute top-4 right-4 z-10 p-3 rounded-full bg-white/80 dark:bg-gray-700/80 backdrop-blur-sm text-gray-600 dark:text-gray-400 hover:text-uum-green dark:hover:text-uum-gold hover:bg-white dark:hover:bg-gray-700 transition-all duration-300 shadow-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <!-- Modal Header -->
                    <div
                        class="p-6 bg-gradient-to-r from-uum-green/10 to-uum-blue/10 dark:from-gray-800 dark:to-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-uum-green to-uum-blue rounded-2xl flex items-center justify-center shadow-xl">
                                <i class="fas fa-search-location text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-uum-green dark:text-uum-gold">UUM Find</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Lost & Found Portal</p>
                            </div>
                        </div>

                        <?php if ($isLoggedIn): ?>
                            <!-- User Profile -->
                            <div class="mt-4 flex items-center space-x-3">
                                <img src="uploads/profile_images/<?php echo htmlspecialchars($userProfileImage); ?>"
                                    alt="Profile"
                                    class="w-10 h-10 rounded-xl object-cover border-2 border-white dark:border-gray-700">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'UUM Member'; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <!-- Navigation Grid -->
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <!-- Home -->
                            <a href="index.php"
                                class="modal-grid-item <?php echo isActivePage('index') ? 'modal-grid-item-active' : ''; ?>">
                                <div
                                    class="modal-grid-icon bg-gradient-to-br from-green-100 to-green-50 dark:from-green-900 dark:to-green-800">
                                    <i class="fas fa-home text-green-600 dark:text-green-400"></i>
                                </div>
                                <span class="modal-grid-label">Home</span>
                            </a>

                            <!-- Lost Items -->
                            <a href="lost-items.php"
                                class="modal-grid-item <?php echo isActivePage('lost-items') ? 'modal-grid-item-active' : ''; ?>">
                                <div
                                    class="modal-grid-icon bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800">
                                    <i class="fas fa-search text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <span class="modal-grid-label">Lost Items</span>
                            </a>

                            <!-- Found Items -->
                            <a href="found-items.php"
                                class="modal-grid-item <?php echo isActivePage('found-items') ? 'modal-grid-item-active' : ''; ?>">
                                <div
                                    class="modal-grid-icon bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900 dark:to-purple-800">
                                    <i class="fas fa-hand-holding text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <span class="modal-grid-label">Found Items</span>
                            </a>

                            <?php if ($isLoggedIn): ?>
                                <!-- Report Item -->
                                <a href="report-item.php"
                                    class="modal-grid-item <?php echo isActivePage('report-item') ? 'modal-grid-item-active' : ''; ?>">
                                    <div
                                        class="modal-grid-icon bg-gradient-to-br from-red-100 to-red-50 dark:from-red-900 dark:to-red-800">
                                        <i class="fas fa-plus-circle text-red-600 dark:text-red-400"></i>
                                    </div>
                                    <span class="modal-grid-label">Report</span>
                                </a>

                                <!-- My Items -->
                                <a href="my-items.php"
                                    class="modal-grid-item <?php echo isActivePage('my-items') ? 'modal-grid-item-active' : ''; ?>">
                                    <div
                                        class="modal-grid-icon bg-gradient-to-br from-yellow-100 to-yellow-50 dark:from-yellow-900 dark:to-yellow-800">
                                        <i class="fas fa-box text-yellow-600 dark:text-yellow-400"></i>
                                    </div>
                                    <span class="modal-grid-label">My Items</span>
                                </a>

                                <!-- Messages -->
                                <a href="messages.php"
                                    class="modal-grid-item <?php echo isActivePage('messages') ? 'modal-grid-item-active' : ''; ?>">
                                    <div
                                        class="modal-grid-icon bg-gradient-to-br from-pink-100 to-pink-50 dark:from-pink-900 dark:to-pink-800">
                                        <i class="fas fa-comments text-pink-600 dark:text-pink-400"></i>
                                    </div>
                                    <span class="modal-grid-label">Messages</span>
                                </a>

                                <!-- Update Profile -->
                                <a href="update-profile.php"
                                    class="modal-grid-item <?php echo isActivePage('update-profile') ? 'modal-grid-item-active' : ''; ?>">
                                    <div
                                        class="modal-grid-icon bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-indigo-900 dark:to-indigo-800">
                                        <i class="fas fa-user-cog text-indigo-600 dark:text-indigo-400"></i>
                                    </div>
                                    <span class="modal-grid-label">Profile</span>
                                </a>

                                <!-- Theme Toggle -->
                                <button id="mobile-theme-toggle" class="modal-grid-item">
                                    <div
                                        class="modal-grid-icon bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800">
                                        <i class="fas fa-palette text-gray-600 dark:text-gray-400"></i>
                                    </div>
                                    <span class="modal-grid-label">Theme</span>
                                </button>
                            <?php else: ?>
                                <!-- Login -->
                                <a href="auth/login.php" class="modal-grid-item">
                                    <div
                                        class="modal-grid-icon bg-gradient-to-br from-green-100 to-green-50 dark:from-green-900 dark:to-green-800">
                                        <i class="fas fa-sign-in-alt text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <span class="modal-grid-label">Login</span>
                                </a>

                                <!-- Register -->
                                <a href="auth/register.php" class="modal-grid-item">
                                    <div
                                        class="modal-grid-icon bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800">
                                        <i class="fas fa-user-plus text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <span class="modal-grid-label">Register</span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <?php if ($isLoggedIn): ?>
                                <a href="auth/logout.php"
                                    class="flex items-center justify-center w-full px-4 py-3 bg-gradient-to-r from-red-100 to-red-200 dark:from-red-900/20 dark:to-red-800/20 text-red-600 dark:text-red-400 rounded-xl hover:from-red-200 hover:to-red-300 dark:hover:from-red-900/30 dark:hover:to-red-800/30 transition-all duration-300 font-medium shadow-sm">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    <span>Logout from UUM Find</span>
                                </a>
                            <?php else: ?>
                                <a href="auth/login.php"
                                    class="flex items-center justify-center w-full px-4 py-3 bg-gradient-to-r from-uum-green to-uum-blue text-white rounded-xl hover:from-green-700 hover:to-blue-700 transition-all duration-300 font-medium shadow-lg">
                                    <i class="fas fa-sign-in-alt mr-3"></i>
                                    <span>Login to UUM Find</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                © <?php echo date('Y'); ?> UUM Lost & Found Portal
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                Version 2.0
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu-overlay"
            class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40 opacity-0 invisible transition-opacity duration-300">
        </div>
    </nav>

    <script src="js/theme.js"></script>