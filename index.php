<?php include 'includes/config.php'; ?>
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
    <!-- Mobile Menu Button -->
    <div class="md:hidden fixed top-4 right-4 z-50">
        <button id="mobile-menu-button"
            class="p-2 rounded-lg bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg">
            <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="fixed inset-0 bg-white dark:bg-gray-800 z-40 transform translate-x-full transition-transform duration-300 md:hidden">
        <div class="flex flex-col h-full p-6">
            <div class="flex justify-between items-center mb-8">
                <a href="index.php" class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-search-location text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-uum-green dark:text-uum-gold">
                            UUM Find
                        </span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found</p>
                    </div>
                </a>
                <button id="close-mobile-menu" class="p-2">
                    <i class="fas fa-times text-gray-600 dark:text-gray-400 text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-6">
                <a href="#features"
                    class="block text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-uum-green transition-colors py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-star mr-3"></i>Features
                </a>
                <a href="#how-it-works"
                    class="block text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-uum-green transition-colors py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-play-circle mr-3"></i>How It Works
                </a>
                <a href="#recent-items"
                    class="block text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-uum-green transition-colors py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-clock mr-3"></i>Recent Items
                </a>
                <a href="#campus-map"
                    class="block text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-uum-green transition-colors py-3 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-map mr-3"></i>Campus Map
                </a>
            </nav>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"
                        class="block w-full bg-uum-green hover:bg-umm-blue text-white text-center py-3 rounded-xl font-medium transition-colors mb-3">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a href="auth/login.php"
                        class="block w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center py-3 rounded-xl font-medium transition-colors mb-3">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="auth/register.php"
                        class="block w-full bg-uum-green hover:bg-umm-blue text-white text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg sticky top-0 z-30 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-search-location text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-uum-green dark:text-uum-gold">
                                UUM Find
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found Portal</p>
                        </div>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <button id="theme-toggle"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-110">
                        <i class="fas fa-moon text-gray-600 dark:text-uum-gold text-lg" id="theme-icon"></i>
                    </button>

                    <div class="hidden md:flex space-x-6">
                        <a href="#features"
                            class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Features</a>
                        <a href="#how-it-works"
                            class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">How
                            It Works</a>
                        <a href="#recent-items"
                            class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Recent
                            Items</a>
                        <a href="#campus-map"
                            class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Campus
                            Map</a>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php"
                            class="bg-uum-green hover:bg-uum-blue text-white px-6 py-2.5 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <div class="flex space-x-3">
                            <a href="auth/login.php"
                                class="text-uum-green hover:text-uum-blue font-medium px-4 py-2 transition-colors">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </a>
                            <a href="auth/register.php"
                                class="bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-6 py-2.5 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <i class="fas fa-user-plus mr-2"></i>Get Started
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section
        class="relative bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 py-12 md:py-20">
        <div class="absolute inset-0 bg-white/20 dark:bg-gray-800/20"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-center">
                <div class="text-center lg:text-left">
                    <div
                        class="inline-flex items-center px-4 py-2 rounded-full bg-uum-green/10 text-uum-green text-sm font-medium mb-6">
                        <i class="fas fa-university mr-2"></i>Universiti Utara Malaysia
                    </div>
                    <h1
                        class="text-3xl md:text-4xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 leading-tight">
                        Lost Something in
                        <span class="text-uum-green dark:text-uum-gold">UUM Campus?</span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-6 md:mb-8 max-w-2xl">
                        UUM's official lost and found portal helps students and staff reunite with their belongings
                        across our beautiful campus.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center lg:justify-start">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php"
                                class="bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl text-center">
                                <i class="fas fa-plus-circle mr-2"></i>Report Item
                            </a>
                        <?php else: ?>
                            <a href="auth/register.php"
                                class="bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl text-center">
                                <i class="fas fa-rocket mr-2"></i>Get Started Free
                            </a>
                            <a href="#features"
                                class="border-2 border-uum-green text-uum-green dark:text-uum-gold hover:bg-uum-green hover:text-white px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 text-center">
                                <i class="fas fa-play-circle mr-2"></i>Learn More
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="mt-8 md:mt-12 grid grid-cols-3 gap-4 md:gap-8 text-center">
                        <div>
                            <div class="text-2xl md:text-3xl font-bold text-uum-green dark:text-uum-gold">1.2K+</div>
                            <div class="text-sm md:text-base text-gray-600 dark:text-gray-400">Items Found</div>
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

                <div class="relative">
                    <div
                        class="relative z-10 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-4 md:p-6 transform rotate-1 hover:rotate-0 transition-transform duration-500">
                        <div
                            class="bg-gradient-to-r from-uum-green to-uum-blue text-white p-3 md:p-4 rounded-xl mb-4 md:mb-6">
                            <h3 class="text-base md:text-lg font-semibold">Recently Found at EDC</h3>
                            <p class="text-green-100 text-sm">MacBook Pro near Library</p>
                        </div>

                        <div class="space-y-3 md:space-y-4">
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div
                                    class="w-10 h-10 md:w-12 md:h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-laptop text-green-600 dark:text-green-400 text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm md:text-base">
                                        Electronics</h4>
                                    <p class="text-xs md:text-sm text-gray-600 dark:text-gray-400 truncate">15 items
                                        found today</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div
                                    class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-book text-blue-600 dark:text-blue-400 text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm md:text-base">Books &
                                        Notes</h4>
                                    <p class="text-xs md:text-sm text-gray-600 dark:text-gray-400 truncate">8 items
                                        found today</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div
                                    class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-key text-purple-600 dark:text-purple-400 text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm md:text-base">Keys &
                                        IDs</h4>
                                    <p class="text-xs md:text-sm text-gray-600 dark:text-gray-400 truncate">12 items
                                        found today</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating elements -->
                    <div
                        class="absolute -top-4 -left-4 w-16 h-16 md:w-24 md:h-24 bg-uum-green/20 dark:bg-uum-green/30 rounded-full opacity-50 animate-float hidden md:block">
                    </div>
                    <div class="absolute -bottom-4 -right-4 w-14 h-14 md:w-20 md:h-20 bg-uum-blue/20 dark:bg-uum-blue/30 rounded-full opacity-50 animate-float hidden md:block"
                        style="animation-delay: 2s;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-12 md:py-20 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">
                    UUM Campus
                    <span class="text-uum-green dark:text-uum-gold">Features</span>
                </h2>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    Designed specifically for UUM students and staff with campus-specific integrations.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <!-- Feature 1 -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 md:p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 md:hover:-translate-y-2">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-r from-uum-green to-uum-blue rounded-2xl flex items-center justify-center mb-4 md:mb-6">
                        <i class="fas fa-user-shield text-white text-xl md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">UUM
                        Authentication</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm md:text-base">
                        Secure login using UUM credentials with additional verification for campus security.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            UUM ID integration
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Campus email verification
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Student/staff role management
                        </li>
                    </ul>
                </div>

                <!-- Feature 2 -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 md:p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 md:hover:-translate-y-2">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 md:mb-6">
                        <i class="fas fa-tachometer-alt text-white text-xl md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">Campus Dashboard
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm md:text-base">
                        Personalized dashboard showing items from your faculty and frequently visited locations.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Faculty-based filtering
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Location preferences
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Quick campus stats
                        </li>
                    </ul>
                </div>

                <!-- Feature 3 -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 md:p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 md:hover:-translate-y-2">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 md:mb-6">
                        <i class="fas fa-map-marked-alt text-white text-xl md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">UUM Campus Map
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm md:text-base">
                        Interactive UUM campus map with building locations and lost item hotspots.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Building locations
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Faculty zones
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Residential colleges
                        </li>
                    </ul>
                </div>

                <!-- Feature 4 -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 md:p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 md:hover:-translate-y-2">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl flex items-center justify-center mb-4 md:mb-6">
                        <i class="fas fa-search text-white text-xl md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">Campus Search
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm md:text-base">
                        Smart search optimized for UUM campus locations, buildings, and common item categories.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Building-based search
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Faculty filtering
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Date range filter
                        </li>
                    </ul>
                </div>

                <!-- Feature 5 -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 md:p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 md:hover:-translate-y-2">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-r from-pink-500 to-rose-600 rounded-2xl flex items-center justify-center mb-4 md:mb-6">
                        <i class="fas fa-images text-white text-xl md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">Image Management
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm md:text-base">
                        Upload and preview images with UUM-branded watermarks for security and identification.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            UUM watermark
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Secure campus uploads
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Thumbnail generation
                        </li>
                    </ul>
                </div>

                <!-- Feature 6 -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 md:p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 md:hover:-translate-y-2">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-r from-teal-500 to-cyan-600 rounded-2xl flex items-center justify-center mb-4 md:mb-6">
                        <i class="fas fa-tasks text-white text-xl md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">Status Tracking
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm md:text-base">
                        Track items with campus-specific status updates and location-based notifications.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Campus location updates
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Faculty notifications
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-uum-green mr-2"></i>
                            Return confirmation
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Campus Map Section -->
    <section id="campus-map" class="py-12 md:py-20 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">
                    UUM Campus
                    <span class="text-uum-green dark:text-uum-gold">Map</span>
                </h2>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    Explore lost and found items across our beautiful UUM campus in Sintok.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-4 md:p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <h3 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-white">Interactive Campus
                            Map</h3>
                        <div class="flex flex-wrap gap-2">
                            <button class="px-3 py-1.5 bg-uum-green text-white rounded-lg text-sm font-medium">
                                <i class="fas fa-lost-item mr-1"></i>Lost Items
                            </button>
                            <button class="px-3 py-1.5 bg-uum-blue text-white rounded-lg text-sm font-medium">
                                <i class="fas fa-found-item mr-1"></i>Found Items
                            </button>
                            <button
                                class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium">
                                <i class="fas fa-filter mr-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div
                    class="h-64 md:h-96 bg-gradient-to-br from-green-100 to-blue-100 dark:from-gray-700 dark:to-gray-800 relative">
                    <!-- Simplified UUM Map Representation -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-map text-4xl md:text-6xl text-uum-green dark:text-uum-gold mb-4"></i>
                            <h4 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-300">UUM Campus Map
                            </h4>
                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm md:text-base">Interactive map
                                loading...</p>
                            <div class="mt-4 inline-flex items-center px-4 py-2 bg-uum-green text-white rounded-lg">
                                <i class="fas fa-sync-alt mr-2"></i>Enable Location Services
                            </div>
                        </div>
                    </div>

                    <!-- Building Markers (Simplified) -->
                    <div class="absolute top-1/4 left-1/4 w-3 h-3 bg-red-500 rounded-full animate-pulse"
                        title="Library"></div>
                    <div class="absolute top-1/3 right-1/4 w-3 h-3 bg-blue-500 rounded-full animate-pulse" title="EDC">
                    </div>
                    <div class="absolute bottom-1/3 left-1/3 w-3 h-3 bg-green-500 rounded-full animate-pulse"
                        title="Student Center"></div>
                </div>

                <div class="p-4 md:p-6 bg-gray-50 dark:bg-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="text-center">
                            <div class="font-semibold text-uum-green">Library Area</div>
                            <div class="text-gray-600 dark:text-gray-400">8 active items</div>
                        </div>
                        <div class="text-center">
                            <div class="font-semibold text-uum-blue">EDC Building</div>
                            <div class="text-gray-600 dark:text-gray-400">12 active items</div>
                        </div>
                        <div class="text-center">
                            <div class="font-semibold text-green-500">Student Center</div>
                            <div class="text-gray-600 dark:text-gray-400">5 active items</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-12 md:py-20 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">
                    How It Works at
                    <span class="text-uum-green dark:text-uum-gold">UUM</span>
                </h2>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    Simple steps designed specifically for UUM students and staff.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="relative mb-4 md:mb-6">
                        <div
                            class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white text-xl md:text-2xl font-bold mx-auto shadow-lg">
                            1
                        </div>
                        <div
                            class="absolute top-0 right-0 w-6 h-6 md:w-8 md:h-8 bg-green-500 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">UUM Account</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm md:text-base">
                        Sign up with your UUM credentials and verify your campus email address.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="relative mb-4 md:mb-6">
                        <div
                            class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-xl md:text-2xl font-bold mx-auto shadow-lg">
                            2
                        </div>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">Report Item</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm md:text-base">
                        Fill item details, upload photos, and pin location on UUM campus map.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="relative mb-4 md:mb-6">
                        <div
                            class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-xl md:text-2xl font-bold mx-auto shadow-lg">
                            3
                        </div>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">Campus Match
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm md:text-base">
                        Get matched with UUM community members and receive campus notifications.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Items Section -->
    <section id="recent-items" class="py-12 md:py-20 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3 md:mb-4">
                    Recently Found at
                    <span class="text-uum-green dark:text-uum-gold">UUM</span>
                </h2>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    Check out some of the latest items reported by our UUM community.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <!-- Sample Item 1 -->
                <div
                    class="bg-white dark:bg-gray-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div
                        class="relative h-40 md:h-48 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800">
                        <div class="absolute top-3 right-3 md:top-4 md:right-4">
                            <span
                                class="bg-red-500 text-white px-2 py-1 md:px-3 md:py-1 rounded-full text-xs md:text-sm font-medium">Lost</span>
                        </div>
                        <div class="absolute bottom-3 left-3 md:bottom-4 md:left-4">
                            <i class="fas fa-laptop text-2xl md:text-4xl text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="p-4 md:p-6">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-2">MacBook Pro 14"</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-3 md:mb-4 text-sm">Silver, with UUM stickers on
                            the cover</p>
                        <div
                            class="flex items-center justify-between text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-1 md:mr-2"></i>
                                <span class="truncate">Library - 2nd Floor</span>
                            </div>
                            <div>2 hours ago</div>
                        </div>
                    </div>
                </div>

                <!-- Sample Item 2 -->
                <div
                    class="bg-white dark:bg-gray-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div
                        class="relative h-40 md:h-48 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800">
                        <div class="absolute top-3 right-3 md:top-4 md:right-4">
                            <span
                                class="bg-green-500 text-white px-2 py-1 md:px-3 md:py-1 rounded-full text-xs md:text-sm font-medium">Found</span>
                        </div>
                        <div class="absolute bottom-3 left-3 md:bottom-4 md:left-4">
                            <i class="fas fa-key text-2xl md:text-4xl text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                    <div class="p-4 md:p-6">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-2">Keychain with USB
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-3 md:mb-4 text-sm">Black leather keychain with 4
                            keys</p>
                        <div
                            class="flex items-center justify-between text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-1 md:mr-2"></i>
                                <span class="truncate">Student Union</span>
                            </div>
                            <div>5 hours ago</div>
                        </div>
                    </div>
                </div>

                <!-- Sample Item 3 -->
                <div
                    class="bg-white dark:bg-gray-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div
                        class="relative h-40 md:h-48 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800">
                        <div class="absolute top-3 right-3 md:top-4 md:right-4">
                            <span
                                class="bg-red-500 text-white px-2 py-1 md:px-3 md:py-1 rounded-full text-xs md:text-sm font-medium">Lost</span>
                        </div>
                        <div class="absolute bottom-3 left-3 md:bottom-4 md:left-4">
                            <i class="fas fa-book text-2xl md:text-4xl text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="p-4 md:p-6">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-2">Calculus Textbook
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-3 md:mb-4 text-sm">Calculus: Early Transcendentals
                        </p>
                        <div
                            class="flex items-center justify-between text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-1 md:mr-2"></i>
                                <span class="truncate">Math Building</span>
                            </div>
                            <div>1 day ago</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-8 md:mt-12">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"
                        class="bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                        <i class="fas fa-eye mr-2"></i>View All Items
                    </a>
                <?php else: ?>
                    <a href="auth/register.php"
                        class="bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                        <i class="fas fa-rocket mr-2"></i>Join UUM Find
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 md:py-20 bg-gradient-to-r from-uum-green to-uum-blue">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-white mb-4 md:mb-6">
                Ready to Find Your Lost Items at UUM?
            </h2>
            <p class="text-lg md:text-xl text-green-100 mb-6 md:mb-8 max-w-2xl mx-auto">
                Join thousands of UUM students and staff who have successfully reunited with their belongings through
                our platform.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"
                        class="bg-white text-uum-green hover:bg-gray-100 px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                        <i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="auth/register.php"
                        class="bg-white text-uum-green hover:bg-gray-100 px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                        <i class="fas fa-user-plus mr-2"></i>Sign Up Free
                    </a>
                    <a href="auth/login.php"
                        class="border-2 border-white text-white hover:bg-white hover:text-uum-green px-6 md:px-8 py-3 md:py-4 rounded-xl font-semibold text-base md:text-lg transition-all duration-300 text-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>UUM Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 md:gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-lg flex items-center justify-center">
                            <i class="fas fa-search-location text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-uum-gold">UUM Find</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Official Lost & Found Portal of Universiti Utara Malaysia. Helping campus communities since
                        2025.
                    </p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-3 md:mb-4">UUM Links</h3>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="https://www.uum.edu.my" class="hover:text-uum-gold transition-colors">UUM Main
                                Website</a></li>
                        <li><a href="https://ecampus.uum.edu.my" class="hover:text-uum-gold transition-colors">UUM
                                e-Campus</a></li>
                        <li><a href="https://library.uum.edu.my" class="hover:text-uum-gold transition-colors">UUM
                                Library</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-3 md:mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-uum-gold transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-uum-gold transition-colors">Contact UUM IT</a></li>
                        <li><a href="#" class="hover:text-uum-gold transition-colors">Privacy Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-3 md:mb-4">Campus Contact</h3>
                    <div class="space-y-2 text-gray-400 text-sm">
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2 text-uum-gold"></i>
                            <span>+604 928 4000</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-uum-gold"></i>
                            <span>find@uum.edu.my</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-uum-gold"></i>
                            <span>Sintok, Kedah</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-6 md:mt-8 pt-6 md:pt-8 text-center text-gray-400 text-sm">
                <p>&copy; 2025 Universiti Utara Malaysia - Lost & Found Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/theme.js"></script>
    <script src="js/mobile-menu.js"></script>
</body>

</html>