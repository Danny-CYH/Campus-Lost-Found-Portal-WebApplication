<?php
// view-myitem.php

// --- 1. ENABLE DEBUGGING (Prevents HTTP 500 blank pages) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 2. SESSION & CONFIG ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
// Safe include for functions
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
}

// --- 3. GET DATA ---
// Get the ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: my-items.php');
    exit;
}

$item_id = $_GET['id'];
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Fetch Item Details
try {
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

    // Security: Only the owner should view this specific management page
    // If not owner, send them to the public view page
    if ($current_user_id != $item['user_id']) {
        header("Location: view-item.php?id=" . $item['id']);
        exit;
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Owner Variables
$is_owner = true;
$can_see_secret = true;

include 'includes/header.php';

// Smart Back Button Logic (Defaults to My Items for this page)
$back_url = 'my-items.php';
$back_text = 'Back to My Items';

// Optional: If they came from dashboard, let them go back there
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'dashboard.php') !== false) {
    $back_url = 'dashboard.php';
    $back_text = 'Back to Dashboard';
}
?>

<!-- Leaflet CSS for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Breadcrumb / Back Button -->
    <div class="mb-6">
        <a href="<?php echo $back_url; ?>" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> <?php echo $back_text; ?>
        </a>
    </div>

    <!-- MAIN CARD CONTAINER -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">

        <div class="grid grid-cols-1 lg:grid-cols-2">

            <!-- LEFT COLUMN: Image & Map -->
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-r border-gray-100 dark:border-gray-700">

                <!-- Large Image -->
                <div class="w-full h-80 bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm mb-6 relative group">
                    <?php if (!empty($item['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($item['title']); ?>"
                            class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105">
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full text-gray-400 flex-col">
                            <i class="fas fa-image text-5xl mb-2"></i>
                            <span>No Image Uploaded</span>
                        </div>
                    <?php endif; ?>

                    <!-- Status Badge Overlay -->
                    <div class="absolute top-4 right-4">
                        <?php
                        $is_returned = isset($item['is_returned']) ? $item['is_returned'] : 0;
                        $badge_color = '';
                        $status_text = '';

                        if ($is_returned == 1) {
                            $badge_color = 'bg-green-600';
                            $status_text = ucfirst($item['status']) . ' - Returned';
                        } else {
                            $badge_color = match ($item['status']) {
                                'lost' => 'bg-red-500',
                                'found' => 'bg-yellow-500 text-yellow-900',
                                default => 'bg-gray-500'
                            };
                            $status_text = ucfirst($item['status']);
                        }
                        ?>
                        <span class="<?php echo $badge_color; ?> text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg capitalize">
                            <?php echo htmlspecialchars($status_text); ?>
                        </span>
                    </div>
                </div>

                <!-- Map View -->
                <?php if (!empty($item['latitude']) && !empty($item['longitude'])): ?>
                    <div class="rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-600">
                        <div id="view-map" class="h-48 w-full z-0"></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT COLUMN: Details -->
            <div class="p-8 flex flex-col h-full">

                <!-- Header -->
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
                        <span>You (<?php echo htmlspecialchars($item['username']); ?>)</span>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700 mb-6">

                <!-- Description -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        <?php echo htmlspecialchars($item['description']); ?>
                    </p>
                </div>

                <!-- Location Info -->
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

                <!-- Secret Identifier (Always Visible to Owner) -->
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

                <!-- MANAGEMENT BUTTONS (The key addition) -->
                <div class="mt-auto pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Manage Item</h3>

                    <div class="grid grid-cols-2 gap-3">

                        <!-- 1. Edit Button -->
                        <?php if ($is_returned == 0): ?>
                            <a href="edit-item.php?id=<?php echo $item['id']; ?>"
                                class="flex items-center justify-center bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md">
                                <i class="fas fa-edit mr-2"></i> Edit Details
                            </a>
                        <?php else: ?>
                            <button disabled class="flex items-center justify-center bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-xl cursor-not-allowed">
                                <i class="fas fa-edit mr-2"></i> Edit Locked
                            </button>
                        <?php endif; ?>

                        <!-- 2. Delete Button -->
                        <?php if ($is_returned == 0): ?>
                            <!-- Active Delete Button (For Active Items) -->
                            <button onclick="deleteItem(<?php echo $item['id']; ?>)"
                                class="flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Item
                            </button>
                        <?php else: ?>
                            <!-- Disabled Delete Button (For Returned Items) -->
                            <button disabled class="flex items-center justify-center bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-xl cursor-not-allowed" title="History items cannot be deleted">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Locked
                            </button>
                        <?php endif; ?>

                    </div>

                    <!-- 3. Mark Returned (Full Width) -->
                    <?php if ($is_returned == 0): ?>
                        <div class="mt-3">
                            <button onclick="markItemReturned(<?php echo $item['id']; ?>)"
                                class="w-full flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md">
                                <i class="fas fa-check-circle mr-2"></i> Mark as Returned
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/theme.js"></script>
<script src="js/app.js"></script> <!-- Reusing app.js for theme logic -->

<script>
    // Initialize Map
    <?php if (!empty($item['latitude']) && !empty($item['longitude'])): ?>
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

    // Delete Logic
    function deleteItem(id) {
        if (confirm('Are you sure you want to PERMANENTLY delete this item? This cannot be undone.')) {
            fetch('api/items.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Item deleted successfully.');
                        window.location.href = 'my-items.php';
                    } else {
                        alert('Error: ' + (data.message || 'Delete failed'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('A network error occurred. Check console.');
                });
        }
    }

    // Mark Returned Logic
    function markItemReturned(id) {
        if (confirm('Mark this item as Returned? This will close the case.')) {
            fetch('api/items.php?action=mark_returned', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `item_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Item marked as returned!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Action failed'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('A network error occurred. Check console.');
                });
        }
    }
</script>
</body>

</html>