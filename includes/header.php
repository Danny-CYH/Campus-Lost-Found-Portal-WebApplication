<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Lost & Found Portal</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAPS_API_KEY; ?>&libraries=places"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <nav class="bg-white dark:bg-gray-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-search-location text-blue-500 text-2xl mr-2"></i>
                        <span class="font-bold text-xl">Campus Lost & Found</span>
                    </a>
                </div>

                <div class="flex items-center">

                    <div class="hidden lg:flex lg:items-center lg:space-x-4" id="desktop-links">
                        <?php if (isset($_SESSION['user_id'])): ?>

                            <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-home mr-1"></i> Home
                            </a>
                            <a href="lost-items.php" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-list-ul mr-1"></i> List Items
                            </a>
                            <a href="chat.php" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-comment-alt mr-1"></i> Message
                            </a>
                            <a href="dashboard.php" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>

                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="admin/panel.php" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-cog mr-1"></i> Admin Panel
                                </a>
                            <?php endif; ?>

                            <button id="theme-toggle" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 p-2 rounded-md">
                                <i class="fas fa-moon" id="theme-icon"></i>
                            </button>

                            <a href="auth/logout.php" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="auth/login.php" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-in-alt mr-1"></i> Login
                            </a>
                            <a href="auth/register.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-user-plus mr-1"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="flex lg:hidden">
                        <button id="mobile-menu-button" type="button" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 p-2 rounded-md focus:outline-none">
                            <i class="fas fa-bars text-xl"></i> </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="lg:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <?php if (isset($_SESSION['user_id'])): ?>

                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="lost-items.php" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-list-ul mr-1"></i> List Items
                    </a>
                    <a href="chat.php" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-comment-alt mr-1"></i> Message
                    </a>
                    <a href="dashboard.php" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin/panel.php" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 block px-3 py-2 rounded-md text-base font-medium">
                            <i class="fas fa-cog mr-1"></i> Admin Panel
                        </a>
                    <?php endif; ?>

                    <a href="auth/logout.php" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 block px-3 py-2 rounded-md text-base font-medium border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="auth/register.php" class="bg-blue-500 hover:bg-blue-600 text-white block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>