<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/points_system.php';

// Get all badges
$stmt = $pdo->query("SELECT * FROM user_badges ORDER BY points_required ASC");
$allBadges = $stmt->fetchAll();

// Get user's badges
$userStats = $pointsSystem->getUserStats($_SESSION['user_id']);
$userBadges = array_column($userStats['badges'], 'name');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badges - Campus Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Badges & Achievements</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Earn badges by helping others and being active in the
                community</p>
            <div class="mt-4 inline-flex items-center px-4 py-2 rounded-full bg-blue-100 text-blue-800">
                <i class="fas fa-award mr-2"></i>
                <?php echo count($userBadges); ?> badges unlocked
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($allBadges as $badge): ?>
                <?php $hasBadge = in_array($badge['badge_name'], $userBadges); ?>
                <div
                    class="bg-white rounded-xl shadow-md p-6 transform transition-all duration-300 
                    <?php echo $hasBadge ? 'border-2 border-yellow-400 hover:scale-105' : 'opacity-75 hover:opacity-100'; ?>">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4 <?php echo $hasBadge ? 'text-yellow-500' : 'text-gray-300'; ?>">
                            <i class="<?php echo $badge['badge_icon']; ?>"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-lg"><?php echo $badge['badge_name']; ?></h3>
                                    <p class="text-gray-600 text-sm mt-1"><?php echo $badge['description']; ?></p>
                                </div>
                                <?php if ($hasBadge): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>
                                        Unlocked
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4">
                                <div class="flex justify-between text-sm text-gray-500 mb-1">
                                    <span>Requirement</span>
                                    <span><?php echo number_format($badge['points_required']); ?> points</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <?php
                                    $progress = min(100, ($userStats['points'] / max(1, $badge['points_required'])) * 100);
                                    ?>
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $progress; ?>%">
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-500 mt-1">
                                    <?php if (!$hasBadge): ?>
                                        Need <?php echo max(0, $badge['points_required'] - $userStats['points']); ?> more points
                                    <?php else: ?>
                                        Unlocked!
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- How to Earn Points Section -->
        <div class="mt-12 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">How to Earn Points</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-6 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl text-blue-600 mb-4">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Report Found Item</h3>
                    <p class="text-gray-600 text-sm">+10 points per item</p>
                </div>

                <div class="text-center p-6 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl text-green-600 mb-4">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Return Item to Owner</h3>
                    <p class="text-gray-600 text-sm">+50 points per return</p>
                </div>

                <div class="text-center p-6 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl text-purple-600 mb-4">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Send Messages</h3>
                    <p class="text-gray-600 text-sm">+5 points per 10 messages</p>
                </div>

                <div class="text-center p-6 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl text-yellow-600 mb-4">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Daily Activity</h3>
                    <p class="text-gray-600 text-sm">+5 points per day active</p>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php" ?>
</body>

</html>