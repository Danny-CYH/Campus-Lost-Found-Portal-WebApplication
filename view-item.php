<?php
// view-item.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// 1. Get the ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php'); // Redirect if no ID provided
    exit;
}

$item_id = $_GET['id'];
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// 2. Fetch Item Details
$stmt = $pdo->prepare("SELECT items.*, users.username, users.email 
                       FROM items 
                       JOIN users ON items.user_id = users.id 
                       WHERE items.id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if item exists
if (!$item) {
    die("Item not found.");
}

// 3. Determine Ownership (To show secret data)
$is_owner = ($current_user_id == $item['user_id']);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$can_see_secret = ($is_owner || $is_admin);

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-6">
        <a href="dashboard.php" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">

        <div class="grid grid-cols-1 lg:grid-cols-2">

            <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-r border-gray-100 dark:border-gray-700">

                <div class="w-full h-80 bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm mb-6 relative group">
                    <?php if ($item['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($item['title']); ?>"
                            class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105">
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full text-gray-400 flex-col">
                            <i class="fas fa-image text-5xl mb-2"></i>
                            <span>No Image Uploaded</span>
                        </div>
                    <?php endif; ?>

                    <div class="absolute top-4 right-4">
                        <?php
                        $badge_color = match ($item['status']) {
                            'lost' => 'bg-red-500',
                            'found' => 'bg-green-500',
                            'returned' => 'bg-blue-500',
                            default => 'bg-gray-500'
                        };
                        ?>
                        <span class="<?php echo $badge_color; ?> text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg capitalize">
                            <?php echo htmlspecialchars($item['status']); ?>
                        </span>
                    </div>
                </div>

                <?php if ($item['latitude'] && $item['longitude']): ?>
                    <div class="rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-600">
                        <div id="view-map" class="h-48 w-full z-0"></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-8">

                <div class="mb-6">
                    <div class="text-sm text-blue-500 dark:text-blue-400 font-bold uppercase tracking-wide mb-1">
                        <?php echo htmlspecialchars($item['category']); ?>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h1>
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span>Posted on <?php echo date('F j, Y', strtotime($item['created_at'])); ?></span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-user mr-2"></i>
                        <span><?php echo htmlspecialchars($item['username']); ?></span>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700 mb-6">

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        <?php echo htmlspecialchars($item['description']); ?>
                    </p>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Location</h3>
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg flex items-start">
                        <i class="fas fa-map-marker-alt text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <span class="font-bold text-gray-800 dark:text-gray-200 block">
                                <?php echo htmlspecialchars($item['location_name']); ?>
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Occurred on: <?php echo date('M d, Y', strtotime($item['date_occurred'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if ($can_see_secret): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            <i class="fas fa-lock text-yellow-500 mr-2"></i>Secret Identifier
                        </h3>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4 rounded-lg">
                            <p class="text-gray-700 dark:text-gray-300 font-mono text-sm">
                                <?php echo htmlspecialchars($item['secret_identifier']); ?>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                * Only you can see this. Use it to verify ownership.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($is_owner && $item['status'] != 'returned'): ?>
                    <div class="mt-8 flex gap-3">
                        <button class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-lg shadow-green-600/30">
                            <i class="fas fa-check mr-2"></i> Mark as Returned
                        </button>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize Map if coordinates exist
    <?php if ($item['latitude'] && $item['longitude']): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('view-map').setView([<?php echo $item['latitude']; ?>, <?php echo $item['longitude']; ?>], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            L.marker([<?php echo $item['latitude']; ?>, <?php echo $item['longitude']; ?>]).addTo(map)
                .bindPopup("<b>Item Location</b><br><?php echo htmlspecialchars($item['location_name']); ?>")
                .openPopup();
        });
    <?php endif; ?>
</script>

<script src="js/theme.js"></script>
<script src="js/app.js"></script>
</body>

</html>