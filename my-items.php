<?php
require_once 'includes/config.php';
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
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

// 2. Fetch User's Items
try {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $my_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
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

    <!-- Header Section (Same theme as Lost/Found Items) -->
    <section class="bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 py-8 md:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        My Reported <span class="text-uum-green dark:text-uum-gold">Items</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Manage your posts, update status, or remove old listings.
                    </p>
                </div>

                <!-- Action Button -->
                <a href="report-item.php" class="inline-flex items-center bg-uum-green hover:bg-uum-blue text-white px-5 py-3 rounded-xl font-medium shadow-lg transition-all transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i> Report New Item
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content Table -->
    <section class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">

                <?php if (!empty($my_items)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Table Header -->
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item Details</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date Posted</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>

                            <!-- Table Body -->
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($my_items as $item): ?>
                                    <?php
                                    $is_returned = isset($item['is_returned']) ? $item['is_returned'] : 0;
                                    $safe_item_json = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">

                                        <!-- Column 1: Image + Title + Category + Location -->
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <!-- Thumbnail -->
                                                <div class="flex-shrink-0 h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 relative">
                                                    <?php if (!empty($item['image_path'])): ?>
                                                        <img class="h-16 w-16 object-cover" src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Item Image">
                                                    <?php else: ?>
                                                        <div class="h-full w-full flex items-center justify-center text-gray-400">
                                                            <i class="fas fa-image text-2xl"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Text Info -->
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                        <?php echo htmlspecialchars($item['title']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                            <?php echo htmlspecialchars($item['category']); ?>
                                                        </span>
                                                        <span class="ml-2 flex items-center inline-block">
                                                            <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                                                            <?php echo htmlspecialchars($item['location_name']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Column 2: Status Badge -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusText = '';
                                            $badgeClass = '';

                                            // 1. Is it Returned?
                                            if ($item['is_returned'] == 1) {
                                                // Yes, it is returned. Check original status to build label.
                                                $originalStatus = ucfirst($item['status']); // "Lost" or "Found"
                                                $statusText = "$originalStatus - Returned";

                                                // Use Green badge for successful return
                                                $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border border-green-200 dark:border-green-800';
                                            } else {
                                                // 2. Not returned yet (Active)
                                                if ($item['status'] == 'lost') {
                                                    $statusText = 'Lost';
                                                    $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-200 dark:border-red-800';
                                                } else {
                                                    $statusText = 'Found';
                                                    $badgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800';
                                                }
                                            }
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php echo $badgeClass; ?>">
                                                <!-- Add Icon based on returned status -->
                                                <?php if ($item['is_returned'] == 1): ?>
                                                    <i class="fas fa-check-circle mr-1.5"></i>
                                                <?php elseif ($item['status'] == 'lost'): ?>
                                                    <i class="fas fa-search mr-1.5"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-hand-holding-heart mr-1.5"></i>
                                                <?php endif; ?>

                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>

                                        <!-- Column 3: Date -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col">
                                                <span class="font-medium"><?php echo date('M j, Y', strtotime($item['created_at'])); ?></span>
                                                <span class="text-xs text-gray-400"><?php echo date('h:i A', strtotime($item['created_at'])); ?></span>
                                            </div>
                                        </td>

                                        <!-- Column 4: Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center space-x-2 opacity-100 sm:opacity-60 group-hover:opacity-100 transition-opacity">

                                                <!-- View -->
                                                <a href="view-myitem.php?id=<?php echo $item['id']; ?>"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                    title="View Details">
                                                    <i class="fas fa-eye text-lg"></i>
                                                </a>

                                                <?php if ($is_returned == 0): ?>
                                                    <!-- Edit -->
                                                    <a href="edit-item.php?id=<?php echo $item['id']; ?>"
                                                        class="p-2 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 rounded-lg transition-colors"
                                                        title="Edit Item">
                                                        <i class="fas fa-edit text-lg"></i>
                                                    </a>

                                                    <!-- Mark Returned -->
                                                    <button onclick="markItemReturned(<?php echo $item['id']; ?>)"
                                                        class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                                                        title="Mark as Returned">
                                                        <i class="fas fa-check-circle text-lg"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <!-- Delete -->
                                                <button onclick="deleteItem(<?php echo $item['id']; ?>)"
                                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                                    title="Delete Item">
                                                    <i class="fas fa-trash-alt text-lg"></i>
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-20">
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-clipboard-list text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No items posted yet</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto">
                            Start by reporting a lost or found item. Your active listings will appear here.
                        </p>
                        <a href="report-item.php" class="inline-flex items-center text-uum-green font-semibold hover:text-uum-blue hover:underline transition-colors">
                            Create your first report &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- EDIT MODAL (Same logic as before, just styled nicely) -->
    <div id="edit-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 transition-all">
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200 dark:border-gray-700 transform scale-100 transition-transform">

            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Item Details</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="edit-form" class="p-6 space-y-5">
                <input type="hidden" id="edit-id" name="id">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Item Name</label>
                    <input type="text" id="edit-title" name="title" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-uum-green focus:border-uum-green outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Category</label>
                    <select id="edit-category" name="category" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-uum-green focus:border-uum-green outline-none transition-all">
                        <option value="Electronics">Electronics</option>
                        <option value="Books & Notes">Books & Notes</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Keys & IDs">Keys & IDs</option>
                        <option value="Bags & Wallets">Bags & Wallets</option>
                        <option value="Water Bottles">Water Bottles</option>
                        <option value="Others">Others</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                    <textarea id="edit-description" name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-uum-green focus:border-uum-green outline-none transition-all"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Location Name</label>
                    <input type="text" id="edit-location" name="location_name" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-uum-green focus:border-uum-green outline-none transition-all">
                </div>

                <div class="flex justify-end pt-2 gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-uum-green hover:bg-uum-blue text-white rounded-xl font-medium shadow-md transition-all transform hover:scale-[1.02]">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/theme.js"></script>
    <script src="js/app.js"></script>
    <script>
        // Modal & Action Logic
        const editModal = document.getElementById('edit-modal');
        const editForm = document.getElementById('edit-form');

        function openEditModal(item) {
            document.getElementById('edit-id').value = item.id;
            document.getElementById('edit-title').value = item.title;
            document.getElementById('edit-category').value = item.category;
            document.getElementById('edit-description').value = item.description;
            document.getElementById('edit-location').value = item.location_name;
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
        }

        function closeEditModal() {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
        }

        // Close modal if clicking outside
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                closeEditModal();
            }
        });

        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api/items.php?action=update', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });

        function deleteItem(id) {
            if (confirm('Are you sure you want to PERMANENTLY delete this item?')) {
                fetch('api/items.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function markItemReturned(id) {
            if (confirm('Mark this item as Returned? This will close the case.')) {
                fetch('api/items.php?action=mark_returned', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `item_id=${id}`
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>

</html>