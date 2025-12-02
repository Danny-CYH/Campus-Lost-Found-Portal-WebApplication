<?php
// Function to check if current page matches nav link
function isActivePage($pageName)
{
    $current_page = basename($_SERVER['PHP_SELF']);

    // Remove query string if present
    $current_page = strtok($current_page, '?');

    // Map page names to their file names
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

// Function to get active class
function getActiveClass($pageName)
{
    return isActivePage($pageName) ? 'text-uum-green dark:text-uum-gold font-semibold border-b-2 border-uum-green' : '';
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
                                <div
                                    class="w-8 h-8 bg-uum-green rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
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
                                    <i class="fas fa-user-cog mr-3"></i>Update Profile
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

        <!-- Mobile Menu (Off-canvas) -->
        <div id="mobile-menu"
            class="md:hidden fixed inset-y-0 right-0 w-full max-w-xs bg-white dark:bg-gray-800 shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
            <div class="flex flex-col h-full">
                <!-- Mobile Menu Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center">
                            <i class="fas fa-search-location text-white"></i>
                        </div>
                        <span class="text-lg font-bold text-uum-green dark:text-uum-gold">UUM Find</span>
                    </div>
                    <button id="close-mobile-menu" type="button"
                        class="p-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-uum-green dark:hover:text-uum-gold">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Mobile Menu Content -->
                <div class="flex-1 overflow-y-auto py-4">
                    <?php if ($isLoggedIn): ?>
                        <!-- User Profile Section (Only show when logged in) -->
                        <div class="px-4 py-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-12 h-12 bg-uum-green rounded-full flex items-center justify-center text-white font-semibold text-xl">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Student/Staff'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Navigation Links -->
                    <div class="px-2 py-4">
                        <div class="space-y-1">
                            <!-- Always show these links -->
                            <a href="index.php"
                                class="mobile-menu-item <?php echo isActivePage('index') ? 'mobile-menu-item-active' : ''; ?>">
                                <i
                                    class="fas fa-home <?php echo isActivePage('index') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                <span>Home</span>
                                <?php if (isActivePage('index')): ?>
                                    <span
                                        class="ml-auto w-2 h-2 bg-uum-green dark:bg-uum-gold rounded-full animate-pulse"></span>
                                <?php endif; ?>
                            </a>
                            <a href="lost-items.php"
                                class="mobile-menu-item <?php echo isActivePage('lost-items') ? 'mobile-menu-item-active' : ''; ?>">
                                <i
                                    class="fas fa-search <?php echo isActivePage('lost-items') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                <span>Lost Items</span>
                                <?php if (isActivePage('lost-items')): ?>
                                    <span
                                        class="ml-auto w-2 h-2 bg-uum-green dark:bg-uum-gold rounded-full animate-pulse"></span>
                                <?php endif; ?>
                            </a>
                            <a href="found-items.php"
                                class="mobile-menu-item <?php echo isActivePage('found-items') ? 'mobile-menu-item-active' : ''; ?>">
                                <i
                                    class="fas fa-hand-holding <?php echo isActivePage('found-items') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                <span>Found Items</span>
                                <?php if (isActivePage('found-items')): ?>
                                    <span
                                        class="ml-auto w-2 h-2 bg-uum-green dark:bg-uum-gold rounded-full animate-pulse"></span>
                                <?php endif; ?>
                            </a>

                            <?php if ($isLoggedIn): ?>
                                <!-- Only show these when user is logged in -->
                                <a href="my-items.php"
                                    class="mobile-menu-item <?php echo isActivePage('my-items') ? 'mobile-menu-item-active' : ''; ?>">
                                    <i
                                        class="fas fa-box <?php echo isActivePage('my-items') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                    <span>My Items</span>
                                    <?php if (isActivePage('my-items')): ?>
                                        <span
                                            class="ml-auto w-2 h-2 bg-uum-green dark:bg-uum-gold rounded-full animate-pulse"></span>
                                    <?php endif; ?>
                                </a>
                                <a href="report-item.php"
                                    class="mobile-menu-item <?php echo isActivePage('report-item') ? 'mobile-menu-item-active' : ''; ?>">
                                    <i
                                        class="fas fa-plus-circle <?php echo isActivePage('report-item') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                    <span>Report Item</span>
                                    <?php if (isActivePage('report-item')): ?>
                                        <span
                                            class="ml-auto w-2 h-2 bg-uum-green dark:bg-uum-gold rounded-full animate-pulse"></span>
                                    <?php endif; ?>
                                </a>
                                <a href="messages.php"
                                    class="mobile-menu-item <?php echo isActivePage('messages') ? 'mobile-menu-item-active' : ''; ?>">
                                    <i
                                        class="fas fa-comments <?php echo isActivePage('messages') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                    <span>Messages</span>
                                    <?php if (isActivePage('messages')): ?>
                                        <span
                                            class="ml-auto w-2 h-2 bg-uum-green dark:bg-uum-gold rounded-full animate-pulse"></span>
                                    <?php endif; ?>
                                </a>
                                <a href="update-profile.php"
                                    class="mobile-menu-item <?php echo isActivePage('update-profile') ? 'mobile-menu-item-active' : ''; ?>">
                                    <i
                                        class="fas fa-user-cog <?php echo isActivePage('update-profile') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                    <span>Update Profile</span>
                                    <?php if (isActivePage('update-profile')): ?>
                                        <span
                                            class="ml-auto w-2 h-2 bg-uum-green dark:bg-uum-gold rounded-full animate-pulse"></span>
                                    <?php endif; ?>
                                </a>
                            <?php else: ?>
                                <!-- Show login/register when NOT logged in -->
                                <a href="auth/login.php"
                                    class="mobile-menu-item <?php echo isActivePage('login') ? 'mobile-menu-item-active' : ''; ?>">
                                    <i
                                        class="fas fa-sign-in-alt <?php echo isActivePage('login') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                    <span>Login</span>
                                </a>
                                <a href="auth/register.php"
                                    class="mobile-menu-item <?php echo isActivePage('register') ? 'mobile-menu-item-active' : ''; ?>">
                                    <i
                                        class="fas fa-user-plus <?php echo isActivePage('register') ? 'text-uum-green dark:text-uum-gold' : 'text-gray-600 dark:text-gray-400'; ?>"></i>
                                    <span>Register</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Theme Toggle in Mobile Menu -->
                    <div class="px-4 py-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-palette text-gray-600 dark:text-gray-400"></i>
                                <span class="text-gray-700 dark:text-gray-300">Theme</span>
                            </div>
                            <button id="mobile-theme-toggle" type="button"
                                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-moon text-gray-600 dark:text-uum-gold"></i>
                                <span id="theme-status"
                                    class="ml-2 text-sm text-gray-600 dark:text-gray-400">Light</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Footer -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <?php if ($isLoggedIn): ?>
                        <a href="auth/logout.php"
                            class="flex items-center justify-center w-full px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            <span class="font-medium">Logout</span>
                        </a>
                    <?php else: ?>
                        <a href="auth/login.php"
                            class="flex items-center justify-center w-full px-4 py-3 bg-uum-green text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-3"></i>
                            <span class="font-medium">Login to UUM Find</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu-overlay"
            class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40 opacity-0 invisible transition-opacity duration-300">
        </div>
    </nav>

    <script src="../js/theme.js"></script>
    <script src="../js/mobile-menu.js"></script>