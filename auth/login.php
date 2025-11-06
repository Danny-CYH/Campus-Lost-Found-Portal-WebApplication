<?php include '../includes/config.php'; ?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Lost & Found Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <a href="../index.php" class="flex items-center justify-center space-x-3 mb-8">
                <div
                    class="w-12 h-12 bg-gradient-to-r from-primary-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-search-location text-white text-xl"></i>
                </div>
                <div>
                    <span
                        class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-blue-700 bg-clip-text text-transparent">
                        CampusFind
                    </span>
                    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-1">Lost & Found Portal</p>
                </div>
            </a>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                Welcome back
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Sign in to your account to continue
            </p>
        </div>

        <!-- Login Form -->
        <form class="mt-8 space-y-6 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl" action="process_login.php"
            method="POST">
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Username or Email
                    </label>
                    <div class="relative">
                        <input id="username" name="username" type="text" required
                            class="relative block w-full px-4 py-3 pl-11 border border-gray-300 dark:border-gray-600 rounded-xl placeholder-gray-500 text-gray-900 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200"
                            placeholder="Enter your username or email">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required
                            class="relative block w-full px-4 py-3 pl-11 border border-gray-300 dark:border-gray-600 rounded-xl placeholder-gray-500 text-gray-900 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200"
                            placeholder="Enter your password">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox"
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-primary-600 hover:text-primary-500 transition-colors">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-primary-500 to-blue-600 hover:from-primary-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-primary-300 group-hover:text-primary-200"></i>
                    </span>
                    Sign in to your account
                </button>
            </div>

            <div class="text-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Don't have an account?
                    <a href="register.php"
                        class="font-medium text-primary-600 hover:text-primary-500 transition-colors">
                        Sign up here
                    </a>
                </span>
            </div>
        </form>

        <!-- Demo Credentials -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">
                <i class="fas fa-info-circle mr-1"></i>Demo Credentials
            </h4>
            <div class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                <div><strong>Username:</strong> demo_user</div>
                <div><strong>Password:</strong> demo123</div>
            </div>
        </div>
    </div>

    <script src="../js/theme.js"></script>
</body>

</html>