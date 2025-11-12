<?php include 'includes/config.php'; ?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UUM Campus Chat - Lost & Found Portal</title>

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

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300 min-h-screen">
    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="lg:hidden fixed inset-0 bg-white dark:bg-gray-800 z-50 transform -translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full p-6">
            <div class="flex justify-between items-center mb-8">
                <a href="index.php" class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
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

            <nav class="flex-1 space-y-4">
                <a href="index.php"
                    class="flex items-center space-x-3 p-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-home w-5"></i>
                    <span>Home</span>
                </a>
                <a href="lost-items.php"
                    class="flex items-center space-x-3 p-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-search w-5"></i>
                    <span>Lost Items</span>
                </a>
                <a href="chat.php"
                    class="flex items-center space-x-3 p-3 rounded-xl bg-uum-green/10 text-uum-green dark:text-uum-gold">
                    <i class="fas fa-comments w-5"></i>
                    <span>Messages</span>
                </a>
                <a href="dashboard.php"
                    class="flex items-center space-x-3 p-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
            </nav>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700 space-y-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="report-item.php"
                        class="block w-full bg-uum-green hover:bg-uum-blue text-white text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-plus-circle mr-2"></i>Report Item
                    </a>
                    <a href="auth/logout.php"
                        class="block w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="auth/login.php"
                        class="block w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="auth/register.php"
                        class="block w-full bg-uum-green hover:bg-uum-blue text-white text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16">
                <!-- Left Section -->
                <div class="flex items-center space-x-4">
                    <button id="mobile-menu-button"
                        class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
                    </button>
                    <a href="index.php" class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-gradient-to-r from-uum-green to-uum-blue rounded-lg flex items-center justify-center">
                            <i class="fas fa-search-location text-white text-sm"></i>
                        </div>
                        <span class="text-lg font-bold text-uum-green dark:text-uum-gold hidden sm:block">UUM
                            Find</span>
                    </a>
                </div>

                <!-- Center Section - Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="index.php"
                        class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Home</a>
                    <a href="lost-items.php"
                        class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Lost
                        Items</a>
                    <a href="chat.php"
                        class="text-uum-green dark:text-uum-gold font-medium border-b-2 border-uum-green">Messages</a>
                    <a href="dashboard.php"
                        class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Dashboard</a>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-3">
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-moon text-gray-600 dark:text-uum-gold" id="theme-icon"></i>
                    </button>

                    <div class="relative">
                        <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 relative">
                            <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
                            <span
                                class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">3</span>
                        </button>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="report-item.php"
                            class="hidden sm:block bg-uum-green hover:bg-uum-blue text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>Report Item
                        </a>
                        <div class="relative">
                            <button
                                class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <div
                                    class="w-8 h-8 bg-uum-green rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
                                <span
                                    class="text-gray-700 dark:text-gray-300 hidden md:block text-sm"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="flex space-x-2">
                            <a href="auth/login.php"
                                class="hidden sm:block text-uum-green hover:text-uum-blue font-medium px-3 py-2 transition-colors">
                                Login
                            </a>
                            <a href="auth/register.php"
                                class="bg-uum-green hover:bg-uum-blue text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                                Join UUM
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex flex-col lg:flex-row h-[80vh] min-h-[600px]">

                <!-- Conversations Sidebar -->
                <div class="lg:w-1/3 xl:w-1/4 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                    <!-- Sidebar Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Messages</h2>
                            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-edit text-uum-green text-sm"></i>
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="relative">
                            <input type="text" placeholder="Search conversations..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Conversations List -->
                    <div class="flex-1 overflow-y-auto">
                        <!-- Conversation Item -->
                        <div
                            class="conversation-item active p-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors bg-uum-green/5">
                            <div class="flex items-center space-x-3">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        AS
                                    </div>
                                    <div
                                        class="absolute bottom-0 right-0 w-2 h-2 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate">Ahmad
                                            Salleh</h3>
                                        <span class="text-xs text-gray-500">2m ago</span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">Hi, I think I found
                                        your laptop...</p>
                                </div>
                            </div>
                        </div>

                        <!-- More conversation items -->
                        <div
                            class="conversation-item p-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        MS
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate">Maria
                                            Soh</h3>
                                        <span class="text-xs text-gray-500">1h ago</span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">About the Calculus
                                        textbook...</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="conversation-item p-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        RJ
                                    </div>
                                    <div
                                        class="absolute bottom-0 right-0 w-2 h-2 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate">Raj
                                            Kumar</h3>
                                        <span class="text-xs text-gray-500">3h ago</span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">Can you describe your
                                        water bottle?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="lg:flex-1 flex flex-col">
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        AS
                                    </div>
                                    <div
                                        class="absolute bottom-0 right-0 w-2 h-2 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Ahmad Salleh</h3>
                                    <p class="text-xs text-green-600 dark:text-green-400 flex items-center">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                                        Online
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-phone text-uum-green text-sm"></i>
                                </button>
                                <button
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-video text-uum-green text-sm"></i>
                                </button>
                                <button
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-info-circle text-uum-green text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div id="messages-container"
                        class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900">
                        <!-- Date Separator -->
                        <div class="flex justify-center my-4">
                            <div class="bg-gray-200 dark:bg-gray-700 px-3 py-1 rounded-full">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Today</span>
                            </div>
                        </div>

                        <!-- Received Message -->
                        <div class="flex items-start space-x-2">
                            <div
                                class="w-8 h-8 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                                AS
                            </div>
                            <div class="flex-1 max-w-[85%]">
                                <div class="bg-white dark:bg-gray-700 rounded-2xl rounded-tl-none px-3 py-2 shadow-sm">
                                    <p class="text-gray-900 dark:text-white text-sm">Hi! I think I found your MacBook
                                        Pro at the library's second floor. Is it silver with UUM stickers?</p>
                                    <span class="text-xs text-gray-500 mt-1 block">2:30 PM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sent Message -->
                        <div class="flex items-start space-x-2 justify-end">
                            <div class="flex-1 max-w-[85%] flex justify-end">
                                <div class="bg-uum-green text-white rounded-2xl rounded-tr-none px-3 py-2 shadow-sm">
                                    <p class="text-sm">Yes, that's exactly it! Thank you so much! Where can I pick it
                                        up?</p>
                                    <span class="text-xs text-green-100 mt-1 block">2:32 PM</span>
                                </div>
                            </div>
                            <div
                                class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                                <?php
                                if (isset($_SESSION['username'])) {
                                    echo strtoupper(substr($_SESSION['username'], 0, 1));
                                } else {
                                    echo 'Y';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- More messages... -->
                    </div>

                    <!-- Message Input -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-2">
                            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-plus text-uum-green text-sm"></i>
                            </button>
                            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-image text-uum-green text-sm"></i>
                            </button>
                            <div class="flex-1 relative">
                                <input type="text" id="message-input" placeholder="Type your message..."
                                    class="w-full pl-3 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green text-sm">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                    <button
                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded transition-colors">
                                        <i class="far fa-smile text-uum-green text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <button id="send-message"
                                class="bg-uum-green hover:bg-uum-blue text-white p-2 rounded-lg transition-colors">
                                <i class="fas fa-paper-plane text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Online Users Sidebar -->
                <div class="hidden xl:block w-80 border-l border-gray-200 dark:border-gray-700 p-4 overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Online Now</h3>

                    <div class="space-y-3">
                        <div
                            class="flex items-center space-x-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                            <div class="relative">
                                <div
                                    class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    AS
                                </div>
                                <div
                                    class="absolute bottom-0 right-0 w-2 h-2 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">Ahmad Salleh</h4>
                                <p class="text-green-600 dark:text-green-400 text-xs">Online</p>
                            </div>
                        </div>

                        <div
                            class="flex items-center space-x-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                            <div class="relative">
                                <div
                                    class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    RJ
                                </div>
                                <div
                                    class="absolute bottom-0 right-0 w-2 h-2 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">Raj Kumar</h4>
                                <p class="text-green-600 dark:text-green-400 text-xs">Online</p>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-4">Offline</h3>

                    <div class="space-y-3">
                        <div
                            class="flex items-center space-x-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                            <div class="relative">
                                <div
                                    class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    MS
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">Maria Soh</h4>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Last seen 2h ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Chat Toggle -->
    <div class="lg:hidden fixed bottom-6 right-6 z-30">
        <button id="mobile-chat-toggle"
            class="bg-uum-green hover:bg-uum-blue text-white p-4 rounded-full shadow-lg transition-colors">
            <i class="fas fa-comments text-lg"></i>
        </button>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/chat_page/chat.js"></script>
</body>

</html>