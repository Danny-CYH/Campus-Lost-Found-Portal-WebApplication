<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    <!-- Footer -->
    <footer class="bg-gray-800 dark:bg-gray-900 text-white py-8 md:py-12">
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
</body>

<script src="../js/theme.js"></script>

</html>