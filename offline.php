<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - UUM Lost & Found</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .offline-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <div
                class="w-20 h-20 mx-auto mb-6 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center offline-pulse">
                <i class="fas fa-wifi-slash text-3xl text-red-600 dark:text-red-400"></i>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">You're Offline</h1>

            <p class="text-gray-600 dark:text-gray-300 mb-6">
                Please check your internet connection. Some features may not be available offline.
            </p>

            <div class="space-y-3">
                <button onclick="window.location.reload()"
                    class="w-full bg-uum-green hover:bg-uum-blue text-white font-medium py-3 rounded-lg transition-colors flex items-center justify-center">
                    <i class="fas fa-sync-alt mr-2"></i> Retry Connection
                </button>

                <button onclick="history.back()"
                    class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium py-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Go Back
                </button>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-info-circle mr-2"></i>
                    Cached pages and basic features are available offline
                </p>
            </div>
        </div>
    </div>

    <script>
        // Try to reconnect every 10 seconds
        setInterval(() => {
            if (navigator.onLine) {
                window.location.reload();
            }
        }, 10000);
    </script>
</body>

</html>