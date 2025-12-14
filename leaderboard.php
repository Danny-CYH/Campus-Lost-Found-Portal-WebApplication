<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/points_system.php';

// Get leaderboard
$leaderboard = $pointsSystem->getLeaderboard(50);
$currentUserRank = null;

// Find current user's rank
foreach ($leaderboard as $index => $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $currentUserRank = $index + 1;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Campus Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for better mobile experience */
        @media (max-width: 640px) {
            .podium-container {
                flex-direction: column;
                align-items: center;
            }

            .podium-item {
                width: 100%;
                max-width: 280px;
                margin-bottom: 1rem;
            }

            .podium-placement {
                transform: scale(0.9);
            }

            .mobile-hide {
                display: none;
            }

            .mobile-show {
                display: block !important;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .user-info-compact {
                flex-direction: column;
                align-items: flex-start;
            }

            .user-rank-badge {
                position: absolute;
                top: 8px;
                right: 8px;
            }
        }

        /* Smooth transitions */
        .leaderboard-row {
            transition: all 0.3s ease;
        }

        .leaderboard-row:hover {
            transform: translateX(4px);
        }

        /* Rank badge colors */
        .rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
        }

        .rank-2 {
            background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
            color: white;
        }

        .rank-3 {
            background: linear-gradient(135deg, #CD7F32, #A0522D);
            color: white;
        }

        .rank-other {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            color: #4b5563;
        }

        /* Podium glow effect */
        .podium-glow {
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
        }

        .dark .podium-glow {
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.5);
        }

        /* Dark mode support */
        .dark .rank-other {
            background: linear-gradient(135deg, #374151, #4b5563);
            color: #d1d5db;
        }

        /* Profile image styles */
        .profile-img-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .profile-img {
            transition: transform 0.3s ease;
        }

        .profile-img:hover {
            transform: scale(1.05);
        }

        /* Ensure images don't overflow */
        img {
            max-width: 100%;
            height: auto;
        }

        /* Add these new styles for better profile image display */
        .podium-profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-image-container {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .initials-fallback {
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        /* Responsive profile image sizes */
        .podium-avatar {
            width: 4rem;
            height: 4rem;
        }

        @media (min-width: 640px) {
            .podium-avatar {
                width: 5rem;
                height: 5rem;
            }
        }

        @media (min-width: 768px) {
            .podium-avatar {
                width: 6rem;
                height: 6rem;
            }
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <!-- Responsive Container -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8">
        <!-- Header Section -->
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                Top Helpers Leaderboard
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 sm:mt-2 text-sm sm:text-base">
                See who's helping the most on campus
            </p>

            <!-- Quick Stats (Mobile) -->
            <div class="mt-4 grid grid-cols-3 gap-2 sm:hidden stats-grid">
                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow text-center">
                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                        <?php echo count($leaderboard); ?>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Helpers</div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow text-center">
                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                        <?php echo number_format($leaderboard[0]['points'] ?? 0); ?>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Top Score</div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow text-center">
                    <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                        <?php echo $currentUserRank ? '#' . $currentUserRank : 'N/A'; ?>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Your Rank</div>
                </div>
            </div>
        </div>

        <!-- Top 3 Podium - Responsive -->
        <?php if (count($leaderboard) >= 3): ?>
            <div class="flex flex-col sm:flex-row justify-center items-end mb-8 sm:mb-12 podium-container">
                <!-- 2nd Place -->
                <div class="text-center mx-2 sm:mx-4 mb-4 sm:mb-0 podium-item order-2 sm:order-1">
                    <div
                        class="podium-avatar mx-auto mb-2 rounded-full flex items-center justify-center rank-2 overflow-hidden border-4 border-white shadow-lg">
                        <div class="profile-image-container">
                            <?php if (!empty($leaderboard[1]['profile_image'])): ?>
                                <img src="uploads/profile_images/<?php echo htmlspecialchars($leaderboard[1]['profile_image']); ?>"
                                    alt="<?php echo htmlspecialchars($leaderboard[1]['username']); ?>"
                                    class="podium-profile-img">
                            <?php else: ?>
                                <div
                                    class="initials-fallback bg-gradient-to-br from-gray-400 to-gray-600 text-white text-xl sm:text-2xl font-bold">
                                    <?php echo strtoupper(substr($leaderboard[1]['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-3 sm:p-4 rounded-lg shadow-md border border-gray-100 dark:border-gray-700">
                        <div class="font-bold text-gray-900 dark:text-white truncate">
                            <?php echo htmlspecialchars($leaderboard[1]['username']); ?>
                        </div>
                        <div class="text-blue-600 dark:text-blue-400 font-semibold text-sm sm:text-base">
                            <?php echo number_format($leaderboard[1]['points']); ?> pts
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-level-up-alt mr-1"></i>Level <?php echo $leaderboard[1]['level']; ?>
                        </div>
                    </div>
                </div>

                <!-- 1st Place -->
                <div class="text-center mx-2 sm:mx-4 mb-4 sm:mb-0 podium-item order-1 sm:order-2">
                    <div class="podium-avatar mx-auto mb-2 rounded-full flex items-center justify-center rank-1 overflow-hidden border-4 border-yellow-300 shadow-2xl podium-glow"
                        style="width: 5rem; height: 5rem;">
                        <div class="profile-image-container">
                            <?php if (!empty($leaderboard[0]['profile_image'])): ?>
                                <img src="uploads/profile_images/<?php echo htmlspecialchars($leaderboard[0]['profile_image']); ?>"
                                    alt="<?php echo htmlspecialchars($leaderboard[0]['username']); ?>"
                                    class="podium-profile-img">
                            <?php else: ?>
                                <div
                                    class="initials-fallback bg-gradient-to-br from-yellow-400 to-orange-500 text-white text-2xl sm:text-3xl font-bold">
                                    <?php echo strtoupper(substr($leaderboard[0]['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg border border-yellow-200 dark:border-yellow-800">
                        <div class="font-bold text-gray-900 dark:text-white text-base sm:text-lg truncate">
                            <i class="fas fa-crown text-yellow-500 mr-1"></i>
                            <?php echo htmlspecialchars($leaderboard[0]['username']); ?>
                        </div>
                        <div class="text-yellow-600 dark:text-yellow-400 font-bold text-base sm:text-xl mt-1">
                            <?php echo number_format($leaderboard[0]['points']); ?> pts
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-level-up-alt mr-1"></i>Level <?php echo $leaderboard[0]['level']; ?>
                        </div>
                    </div>
                </div>

                <!-- 3rd Place -->
                <div class="text-center mx-2 sm:mx-4 podium-item order-3">
                    <div
                        class="podium-avatar mx-auto mb-2 rounded-full flex items-center justify-center rank-3 overflow-hidden border-4 border-white shadow-lg">
                        <div class="profile-image-container">
                            <?php if (!empty($leaderboard[2]['profile_image'])): ?>
                                <img src="uploads/profile_images/<?php echo htmlspecialchars($leaderboard[2]['profile_image']); ?>"
                                    alt="<?php echo htmlspecialchars($leaderboard[2]['username']); ?>"
                                    class="podium-profile-img">
                            <?php else: ?>
                                <div
                                    class="initials-fallback bg-gradient-to-br from-amber-700 to-amber-900 text-white text-xl sm:text-2xl font-bold">
                                    <?php echo strtoupper(substr($leaderboard[2]['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-3 sm:p-4 rounded-lg shadow-md border border-gray-100 dark:border-gray-700">
                        <div class="font-bold text-gray-900 dark:text-white truncate">
                            <?php echo htmlspecialchars($leaderboard[2]['username']); ?>
                        </div>
                        <div class="text-blue-600 dark:text-blue-400 font-semibold text-sm sm:text-base">
                            <?php echo number_format($leaderboard[2]['points']); ?> pts
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-level-up-alt mr-1"></i>Level <?php echo $leaderboard[2]['level']; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Full Leaderboard - Responsive Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
            <!-- Header -->
            <div
                class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-list-ol mr-2"></i>All Helpers
                </h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <?php echo min(50, count($leaderboard)); ?> of <?php echo count($leaderboard); ?>
                </span>
            </div>

            <!-- Leaderboard Rows -->
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($leaderboard as $index => $user): ?>
                    <div class="leaderboard-row px-3 sm:px-4 md:px-6 py-3 sm:py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 
            <?php echo $user['id'] == $_SESSION['user_id'] ? 'bg-blue-50 dark:bg-blue-900/20' : ''; ?>">
                        <div class="flex items-center">
                            <!-- Rank Number (Always Visible) -->
                            <div class="flex-shrink-0 w-10 sm:w-12 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm sm:text-base
                        <?php echo $index < 3 ? 'rank-' . ($index + 1) : 'rank-other'; ?>">
                                    <?php echo $index + 1; ?>
                                </span>
                            </div>

                            <!-- User Info -->
                            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                                <div class="flex items-center">
                                    <!-- Profile Image -->
                                    <div class="flex-shrink-0 mr-3">
                                        <div
                                            class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200 dark:border-gray-700 shadow-sm">
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <img src="uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                                    alt="<?php echo htmlspecialchars($user['username']); ?>"
                                                    class="w-full h-full object-cover profile-img">
                                            <?php else: ?>
                                                <div
                                                    class="w-full h-full flex items-center justify-center bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="font-medium text-gray-900 dark:text-white truncate">
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <span
                                                            class="ml-2 text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-1 rounded user-rank-badge">You</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Stats (Stack on mobile, inline on desktop) -->
                                            <div class="mt-1 sm:mt-0 flex flex-wrap gap-3">
                                                <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                    <i class="fas fa-level-up-alt mr-1 text-gray-400"></i>
                                                    Lvl <?php echo $user['level']; ?>
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                    <i class="fas fa-star mr-1 text-yellow-500"></i>
                                                    <?php echo number_format($user['points']); ?> pts
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Points (Hidden on small mobile, shown on medium+) -->
                            <div class="ml-4 hidden md:block">
                                <div class="text-right">
                                    <div class="font-bold text-gray-900 dark:text-white text-lg">
                                        <?php echo number_format($user['points']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">points</div>
                                </div>
                            </div>

                            <!-- Points (Visible on mobile only) -->
                            <div class="ml-2 md:hidden">
                                <div class="text-right">
                                    <div class="font-bold text-gray-900 dark:text-white text-sm">
                                        <?php echo number_format($user['points']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">pts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Your Rank Section - Responsive -->
        <?php if ($currentUserRank): ?>
            <?php
            $userStats = $pointsSystem->getUserStats($_SESSION['user_id']);
            $nextRankPoints = $currentUserRank > 1 ? $leaderboard[$currentUserRank - 2]['points'] - $userStats['points'] : 0;
            ?>

            <div
                class="mt-6 sm:mt-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <div class="text-center sm:text-left mb-4 sm:mb-0">
                        <h3 class="text-lg sm:text-xl font-bold flex items-center">
                            <i class="fas fa-user-circle mr-2"></i>
                            Your Current Rank
                        </h3>
                        <p class="opacity-90 text-sm sm:text-base mt-1">
                            Keep helping to move up the leaderboard!
                        </p>

                        <!-- Progress Info (Mobile only) -->
                        <?php if ($currentUserRank > 1): ?>
                            <p class="text-xs sm:text-sm mt-2 sm:hidden bg-white/20 p-2 rounded-lg">
                                <i class="fas fa-arrow-up mr-1"></i>
                                Need <?php echo number_format($nextRankPoints); ?> pts for rank
                                #<?php echo $currentUserRank - 1; ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-bold">#<?php echo $currentUserRank; ?></div>
                        <div class="text-xs sm:text-sm opacity-80 mt-1">
                            out of <?php echo count($leaderboard); ?> users
                        </div>
                        <div class="mt-2 text-xs">
                            <i class="fas fa-star mr-1"></i>
                            <?php echo number_format($userStats['points']); ?> total points
                        </div>
                    </div>
                </div>

                <!-- Progress Info (Desktop only) -->
                <?php if ($currentUserRank > 1): ?>
                    <div class="mt-4 pt-4 border-t border-white/20 hidden sm:block">
                        <div class="flex items-center justify-between">
                            <p class="text-sm">
                                <i class="fas fa-arrow-up mr-1"></i>
                                You need <?php echo number_format($nextRankPoints); ?> more points to reach rank
                                #<?php echo $currentUserRank - 1; ?>
                            </p>
                            <?php if ($currentUserRank <= 10): ?>
                                <span class="text-xs bg-white/30 px-3 py-1 rounded-full">
                                    Top 10 Contender!
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Progress Bar -->
                        <?php if ($currentUserRank > 1 && $nextRankPoints > 0): ?>
                            <div class="mt-3">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Your Rank: #<?php echo $currentUserRank; ?></span>
                                    <span>Next Rank: #<?php echo $currentUserRank - 1; ?></span>
                                </div>
                                <div class="w-full bg-white/20 rounded-full h-2">
                                    <?php
                                    $progressPercent = min(100, ($userStats['points'] / $leaderboard[$currentUserRank - 2]['points']) * 100);
                                    ?>
                                    <div class="bg-yellow-400 h-2 rounded-full" style="width: <?php echo $progressPercent; ?>%">
                                    </div>
                                </div>
                                <div class="text-right text-xs mt-1">
                                    <?php echo number_format($userStats['points']); ?> /
                                    <?php echo number_format($leaderboard[$currentUserRank - 2]['points']); ?> points
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Tips Section (Mobile only) -->
        <div class="mt-6 sm:hidden bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow p-4 text-white">
            <h4 class="font-bold text-sm flex items-center mb-2">
                <i class="fas fa-lightbulb mr-2"></i>
                Quick Tips to Rank Up
            </h4>
            <ul class="text-xs space-y-1">
                <li class="flex items-center">
                    <i class="fas fa-check-circle mr-2 text-sm"></i>
                    Return found items (+50 pts each)
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check-circle mr-2 text-sm"></i>
                    Report lost/found items (+10 pts)
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check-circle mr-2 text-sm"></i>
                    Help others in messages (+5 pts)
                </li>
            </ul>
        </div>
    </div>

    <?php require_once "includes/footer.php" ?>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function () {
            // Highlight current user row
            const userRows = document.querySelectorAll('.leaderboard-row');
            userRows.forEach(row => {
                if (row.querySelector('.user-rank-badge')) {
                    row.classList.add('border-l-4', 'border-blue-500');
                }

                // Add click effect
                row.addEventListener('click', function () {
                    this.style.transform = 'scale(0.99)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // Podium hover effect
            const podiumItems = document.querySelectorAll('.podium-item');
            podiumItems.forEach(item => {
                item.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-5px)';
                });
                item.addEventListener('mouseleave', function () {
                    this.style.transform = '';
                });
            });

            // Profile image hover effect
            const profileImages = document.querySelectorAll('.profile-img');
            profileImages.forEach(img => {
                img.addEventListener('mouseenter', function () {
                    this.style.transform = 'scale(1.1)';
                });
                img.addEventListener('mouseleave', function () {
                    this.style.transform = 'scale(1)';
                });
            });

            // Responsive adjustments
            function handleResize() {
                const isMobile = window.innerWidth < 640;
                const podiumContainer = document.querySelector('.podium-container');

                if (podiumContainer && isMobile) {
                    // Mobile: adjust podium spacing
                    podiumContainer.classList.add('space-y-4');
                } else if (podiumContainer) {
                    podiumContainer.classList.remove('space-y-4');
                }
            }

            window.addEventListener('resize', handleResize);
            handleResize(); // Initial check
        });
    </script>
</body>

</html>