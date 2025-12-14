<?php
require_once 'includes/config.php';
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
}
if (file_exists('includes/points_system.php')) {
    require_once 'includes/points_system.php';
}

// 1. Security Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all point history (increase limit or get all)
$pointHistory = $pointsSystem->getPointHistory($user_id, 100); // Get up to 100 records

// Get user stats
$userStats = $pointsSystem->getUserStats($user_id);

// Calculate summary statistics
$totalEarned = 0;
$totalSpent = 0;
$transactionsBySource = [];

foreach ($pointHistory as $transaction) {
    if ($transaction['transaction_type'] == 'earned') {
        $totalEarned += $transaction['points'];
    } elseif ($transaction['transaction_type'] == 'spent') {
        $totalSpent += $transaction['points'];
    }

    // Group by source
    $source = $transaction['source'];
    if (!isset($transactionsBySource[$source])) {
        $transactionsBySource[$source] = 0;
    }
    $transactionsBySource[$source] += ($transaction['transaction_type'] == 'earned' ? 1 : -1) * $transaction['points'];
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #e5e7eb;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.5s ease-in-out;
        }

        .level-indicator {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .badge-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .badge-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 8px;
        }

        .dashboard-nav a {
            transition: all 0.3s ease;
        }

        .dashboard-nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .dashboard-nav a.active {
            background-color: rgba(255, 255, 255, 0.25);
            font-weight: 600;
        }

        .transaction-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .filter-btn.active {
            background-color: #3b82f6;
            color: white;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: #f3f4f6;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
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

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- Header Section -->
    <section class="bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 py-6 md:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Points <span class="text-uum-green dark:text-uum-gold">History</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Track all your points activity and earnings.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left Column: Points Summary -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Points Summary Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 stat-card">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Points Summary</h2>

                    <div class="space-y-4">
                        <!-- Total Points -->
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                                <?php echo number_format($userStats['points']); ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Current Points</div>
                        </div>

                        <!-- Level Indicator -->
                        <div class="text-center">
                            <div class="level-indicator mx-auto mb-2">
                                Lv. <?php echo $userStats['level']; ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Current Level</div>

                            <!-- Progress to next level -->
                            <div class="mt-3">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Level <?php echo $userStats['level']; ?></span>
                                    <span>Level <?php echo $userStats['level'] + 1; ?></span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill"
                                        style="width: <?php echo ($userStats['points'] % 100); ?>%"></div>
                                </div>
                                <div class="text-right text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    <?php echo 100 - ($userStats['points'] % 100); ?> points to next level
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div class="text-center p-3 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                <div class="text-xl font-bold text-green-600 dark:text-green-400">
                                    <?php echo number_format($totalEarned); ?>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Total Earned</div>
                            </div>
                            <div class="text-center p-3 bg-red-50 dark:bg-red-900/30 rounded-lg">
                                <div class="text-xl font-bold text-red-600 dark:text-red-400">
                                    <?php echo number_format($totalSpent); ?>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Total Spent</div>
                            </div>
                        </div>

                        <!-- Total Transactions -->
                        <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                            <div class="text-xl font-bold text-purple-600 dark:text-purple-400">
                                <?php echo $userStats['total_transactions']; ?>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Total Actions</div>
                        </div>
                    </div>
                </div>

                <!-- Source Breakdown -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Earnings by Source</h2>

                    <div class="space-y-3">
                        <?php if (!empty($transactionsBySource)): ?>
                            <?php foreach ($transactionsBySource as $source => $points): ?>
                                <?php if ($points > 0): ?>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?php echo ucfirst(str_replace('_', ' ', $source)); ?>
                                            </span>
                                        </div>
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                            +<?php echo number_format($points); ?> pts
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-2">
                                No point sources recorded yet
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Points History -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <!-- Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">All Points Transactions</h2>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                    Showing <?php echo count($pointHistory); ?> transactions
                                </p>
                            </div>

                            <!-- Filters -->
                            <div class="flex space-x-2">
                                <button onclick="filterTransactions('all')"
                                    class="filter-btn px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 active">
                                    All
                                </button>
                                <button onclick="filterTransactions('earned')"
                                    class="filter-btn px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Earned
                                </button>
                                <button onclick="filterTransactions('spent')"
                                    class="filter-btn px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Spent
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions List -->
                    <div class="divide-y divide-gray-200 dark:divide-gray-700" id="transactions-container">
                        <?php if (count($pointHistory) > 0): ?>
                            <?php foreach ($pointHistory as $transaction): ?>
                                <div class="transaction-row px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200"
                                    data-type="<?php echo $transaction['transaction_type']; ?>">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <!-- Icon -->
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-12 h-12 rounded-full flex items-center justify-center 
                                                    <?php echo $transaction['transaction_type'] == 'earned'
                                                        ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300'
                                                        : ($transaction['transaction_type'] == 'spent'
                                                            ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300'
                                                            : 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300'); ?>">
                                                    <?php if ($transaction['transaction_type'] == 'earned'): ?>
                                                        <i class="fas fa-plus text-lg"></i>
                                                    <?php elseif ($transaction['transaction_type'] == 'spent'): ?>
                                                        <i class="fas fa-minus text-lg"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-exclamation text-lg"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Details -->
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <h3 class="font-medium text-gray-900 dark:text-white">
                                                        <?php echo ucfirst(str_replace('_', ' ', $transaction['source'])); ?>
                                                    </h3>
                                                    <?php if ($transaction['item_title']): ?>
                                                        <span
                                                            class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                                                            <?php echo htmlspecialchars($transaction['item_title']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($transaction['description']): ?>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                        <?php echo htmlspecialchars($transaction['description']); ?>
                                                    </p>
                                                <?php endif; ?>

                                                <div class="flex items-center space-x-3 mt-1">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        <i class="far fa-clock mr-1"></i>
                                                        <?php echo date('F j, Y g:i A', strtotime($transaction['created_at'])); ?>
                                                    </span>
                                                    <span
                                                        class="text-xs px-2 py-0.5 rounded-full 
                                                        <?php echo $transaction['transaction_type'] == 'earned'
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                            : ($transaction['transaction_type'] == 'spent'
                                                                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'); ?>">
                                                        <?php echo ucfirst($transaction['transaction_type']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Points -->
                                        <div class="text-right">
                                            <div class="text-xl font-bold 
                                                <?php echo $transaction['transaction_type'] == 'earned'
                                                    ? 'text-green-600 dark:text-green-400'
                                                    : ($transaction['transaction_type'] == 'spent'
                                                        ? 'text-red-600 dark:text-red-400'
                                                        : 'text-yellow-600 dark:text-yellow-400'); ?>">
                                                <?php echo $transaction['transaction_type'] == 'earned' ? '+' : '-'; ?>
                                                <?php echo number_format($transaction['points']); ?> pts
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Transaction #<?php echo $transaction['id']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Empty State -->
                            <div class="text-center py-16">
                                <div
                                    class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-history text-3xl text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-white">No points history yet</h3>
                                <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-md mx-auto">
                                    Start earning points by reporting found items, helping others, and participating in the
                                    community.
                                </p>
                                <div class="mt-6 space-x-4">
                                    <a href="report-item.php?type=found"
                                        class="inline-flex items-center bg-uum-green hover:bg-uum-blue text-white px-5 py-2.5 rounded-lg font-medium">
                                        <i class="fas fa-map-marker-alt mr-2"></i> Report Found Item
                                    </a>
                                    <a href="dashboard.php"
                                        class="inline-flex items-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-5 py-2.5 rounded-lg font-medium">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination (if needed) -->
                    <?php if (count($pointHistory) >= 100): ?>
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Showing <?php echo min(100, count($pointHistory)); ?> of
                                    <?php echo count($pointHistory); ?> transactions
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        class="pagination-btn px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        disabled>
                                        <i class="fas fa-chevron-left mr-1"></i> Previous
                                    </button>
                                    <button
                                        class="pagination-btn px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        onclick="loadMoreTransactions()">
                                        Load More <i class="fas fa-chevron-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tips Card -->
                <div
                    class="mt-6 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i> How to Earn More Points
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-green-600 dark:text-green-300"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Return Found Items</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Earn 50 points for every found item
                                    you return</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <i class="fas fa-flag text-blue-600 dark:text-blue-300"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Report Items</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Get points for reporting lost and
                                    found items</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                <i class="fas fa-comments text-purple-600 dark:text-purple-300"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Help Others</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Communicate with others to help
                                    return items</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-yellow-600 dark:text-yellow-300"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Daily Activity</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Stay active daily to earn bonus
                                    points</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="../js/theme.js"></script>
    <script>
        // Filter transactions by type
        function filterTransactions(type) {
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Show/hide transactions
            const transactions = document.querySelectorAll('.transaction-row');
            transactions.forEach(transaction => {
                if (type === 'all' || transaction.dataset.type === type) {
                    transaction.style.display = 'block';
                } else {
                    transaction.style.display = 'none';
                }
            });

            // Update count
            const visibleCount = document.querySelectorAll('.transaction-row[style="display: block;"]').length;
            const countElement = document.querySelector('#transactions-container').previousElementSibling.querySelector('p');
            if (countElement) {
                countElement.textContent = `Showing ${visibleCount} transactions`;
            }
        }

        // Load more transactions (for future implementation)
        function loadMoreTransactions() {
            // This would typically make an AJAX request to load more transactions
            alert('Loading more transactions... This feature would load additional records via AJAX.');
            // You could implement pagination here
        }

        // Smooth scroll for dashboard navigation
        document.querySelectorAll('.dashboard-nav a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function () {
            // Add hover effects to transaction rows
            const rows = document.querySelectorAll('.transaction-row');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.05)';
                });
                row.addEventListener('mouseleave', function () {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>

</html>