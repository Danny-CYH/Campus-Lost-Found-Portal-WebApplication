<?php include '../includes/config.php'; ?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UUM Campus Lost & Found Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/styles.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        uum: {
                            green: '#006837',
                            gold: '#FFD700',
                            blue: '#0056b3',
                            light: {
                                green: '#e8f5e8',
                                blue: '#e6f0fa'
                            }
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

<body
    class="bg-gradient-to-br from-uum-light-green to-uum-light-blue dark:from-gray-800 dark:to-gray-900 min-h-screen flex items-center justify-center p-4">
    <!-- Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-uum-green/10 rounded-full animate-float"></div>
        <div class="absolute top-1/4 -right-16 w-32 h-32 bg-uum-blue/10 rounded-full animate-float"
            style="animation-delay: 2s;"></div>
        <div class="absolute bottom-32 -left-16 w-40 h-40 bg-uum-green/5 rounded-full animate-float"
            style="animation-delay: 4s;"></div>
        <div class="absolute -bottom-20 -right-20 w-56 h-56 bg-uum-blue/5 rounded-full animate-float"
            style="animation-delay: 1s;"></div>
    </div>

    <div class="w-full max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Left Side - Brand & Info -->
            <div class="text-center lg:text-left space-y-8">
                <!-- Header -->
                <div class="space-y-4">
                    <a href="../index.php" class="inline-flex items-center space-x-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-r from-uum-green to-uum-blue rounded-2xl flex items-center justify-center shadow-2xl">
                            <i class="fas fa-search-location text-white text-2xl"></i>
                        </div>
                        <div class="text-left">
                            <h1 class="text-3xl lg:text-4xl font-bold text-uum-green dark:text-uum-gold">
                                UUM Find
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 text-sm lg:text-base">Lost & Found Portal</p>
                        </div>
                    </a>
                </div>

                <!-- Features -->
                <div class="space-y-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                        Reconnect with Your
                        <span class="text-uum-green dark:text-uum-gold">Lost Belongings</span>
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-md">
                        Access your UUM Campus Find account to manage lost items, connect with finders, and help others
                        reunite with their belongings.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            class="flex items-center space-x-3 p-3 bg-white/50 dark:bg-gray-800/50 rounded-xl backdrop-blur-sm">
                            <div class="w-10 h-10 bg-uum-green/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-uum-green"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Secure Login</span>
                        </div>
                        <div
                            class="flex items-center space-x-3 p-3 bg-white/50 dark:bg-gray-800/50 rounded-xl backdrop-blur-sm">
                            <div class="w-10 h-10 bg-uum-blue/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bolt text-uum-blue"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Instant Access</span>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-uum-green dark:text-uum-gold">1.2K+</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Items Found</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-uum-blue">89%</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Return Rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">24/7</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Active</div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/20 p-8">
                <!-- Form Header -->
                <div class="text-center mb-8">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                        Welcome Back
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Sign in to your UUM Campus account
                    </p>
                </div>

                <!-- Login Form -->
                <form class="space-y-6" action="../api/process_login.php" method="POST">
                    <!-- Resend Verification -->
                    <?php if (isset($_SESSION['resend_verification'])): ?>
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-center space-x-2 mb-2">
                                <i class="fas fa-envelope text-blue-600"></i>
                                <h3 class="font-semibold text-blue-800">Resend Verification Email</h3>
                            </div>
                            <p class="text-sm text-blue-700 mb-3">
                                Didn't receive the verification email? Enter your email below to receive a new one.
                            </p>
                            <form action="resend_verification.php" method="POST" class="flex space-x-2">
                                <input type="email" name="email" required placeholder="Enter your email address"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-uum-green">
                                <button type="submit"
                                    class="bg-uum-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-uum-blue transition-colors">
                                    Resend
                                </button>
                            </form>
                        </div>
                        <?php
                        unset($_SESSION['resend_verification']);
                    endif;
                    ?>

                    <div class="space-y-4">
                        <!-- Username/Email Field -->
                        <div>
                            <label for="username"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-user mr-2 text-uum-green"></i>
                                Username or Email
                            </label>
                            <div class="relative">
                                <input id="username" name="username" type="text" required
                                    class="w-full pl-4 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200"
                                    placeholder="Enter your username or email">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-check-circle text-green-500 opacity-0 transition-opacity duration-200"
                                        id="username-check"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2 text-uum-green"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required
                                    class="w-full pl-4 pr-12 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200"
                                    placeholder="Enter your password">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    id="toggle-password">
                                    <i class="fas fa-eye text-gray-400 hover:text-uum-green transition-colors"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember_me" type="checkbox"
                                class="w-4 h-4 text-uum-green focus:ring-uum-green border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Remember me
                            </label>
                        </div>

                        <a href="#" class="text-sm font-medium text-uum-green hover:text-uum-blue transition-colors">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white py-3 px-4 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-uum-green focus:ring-offset-2">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign in to your account
                    </button>

                    <!-- Register Link -->
                    <div class="text-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?
                            <a href="register.php"
                                class="font-semibold text-uum-green hover:text-uum-blue transition-colors">
                                Create one here
                            </a>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/theme.js"></script>
    <script src="../js/login/login.js"></script>

</body>

</html>