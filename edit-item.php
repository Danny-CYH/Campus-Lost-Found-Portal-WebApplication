<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// 3. GET ITEM ID & FETCH DATA
if (!isset($_GET['id'])) {
    echo "<script>alert('No item specified.'); window.location.href='my-items.php';</script>";
    exit();
}

$item_id = $_GET['id'];

// Fetch existing item data securely
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "<script>alert('Item not found or you do not have permission to edit it.'); window.location.href='my-items.php';</script>";
    exit();
}

// 4. DEFINE OPTIONS (Same as Report Page)
$categories_list = [
    "Electronics",
    "Books & Notes",
    "Clothing",
    "Accessories",
    "Keys & IDs",
    "Bags & Wallets",
    "Water Bottles",
    "Others"
];

$locations_data = [
    "DKG 1" => ["lat" => 6.466039027050011, "lng" => 100.5078677492091],
    "DKG 2" => ["lat" => 6.4647742594628586, "lng" => 100.50798330995569],
    "DKG 3" => ["lat" => 6.4661107426425515, "lng" => 100.50581076976913],
    "DKG 4" => ["lat" => 6.467700622959108, "lng" => 100.50795079373322],
    "DKG 5" => ["lat" => 6.4586947069409035, "lng" => 100.50587896329803],
    "DKG 6" => ["lat" => 6.45559717337014, "lng" => 100.50464284022831],
    "DKG 7" => ["lat" => 6.4538447845452, "lng" => 100.4995698090767],
    "DKG 8" => ["lat" => 6.455723956980103, "lng" => 100.4986244207178],
    "Laluan A" => ["lat" => 6.458164107181955, "lng" => 100.50156375376041],
    "Laluan B" => ["lat" => 6.462486396849043, "lng" => 100.50405586680641],
    "Laluan C" => ["lat" => 6.480566556610062, "lng" => 100.50843323185404],
    "Laluan D" => ["lat" => 6.438846176083953, "lng" => 100.53073822003874],
    "Main Library" => ["lat" => 6.463514704776473, "lng" => 100.50537757514739],
    "Masjid" => ["lat" => 6.463181232234549, "lng" => 100.49870978461347],
    "Pusat Sukan" => ["lat" => 6.474716539969105, "lng" => 100.50443945004015],
    "Varsity Mall" => ["lat" => 6.46288196027126, "lng" => 100.50094032818744],
];

// 5. PARSE EXISTING LOCATION (Split "Name (Details)" back into two parts)
$current_main_location = $item['location_name'];
$current_location_details = "";

// Regex to check if it fits the pattern "Location Name (Details)"
if (preg_match('/^(.*?) \((.*)\)$/', $item['location_name'], $matches)) {
    // Only split if the first part is actually in our list of locations
    if (array_key_exists($matches[1], $locations_data)) {
        $current_main_location = $matches[1];
        $current_location_details = $matches[2];
    }
}

// 6. HANDLE FORM SUBMISSION (UPDATE)
if (isset($_POST['update_item_btn'])) {

    // Get Inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $date_occurred = $_POST['date_occurred'];
    $status = $_POST['status'];
    $secret_identifier = trim($_POST['secret_identifier']);

    // --- NEW LOCATION LOGIC ---
    $main_location = trim($_POST['location_name']);
    $specific_spot = trim($_POST['location_details']);

    // Combine them: "DKG 1 (Room 101)"
    if (!empty($specific_spot)) {
        $location_name_to_save = $main_location . " (" . $specific_spot . ")";
    } else {
        $location_name_to_save = $main_location;
    }
    // ---------------------------

    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    // Handle Image Update
    $image_path = $item['image_path'];

    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $target_dir = "uploads/items/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["item_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $msg = "Failed to upload new image.";
                $msg_type = "error";
            }
        } else {
            $msg = "Only JPG, JPEG, PNG & GIF files allowed.";
            $msg_type = "error";
        }
    }

    // Update Database
    if ($msg == "") {
        try {
            $sql = "UPDATE items SET 
                    title = :title, 
                    description = :desc, 
                    category = :cat, 
                    status = :status, 
                    location_name = :loc_name, 
                    latitude = :lat, 
                    longitude = :lng, 
                    image_path = :img, 
                    secret_identifier = :secret, 
                    date_occurred = :date_occ
                    WHERE id = :id AND user_id = :uid";

            $stmt = $pdo->prepare($sql);
            $params = [
                ':title' => $title,
                ':desc' => $description,
                ':cat' => $category,
                ':status' => $status,
                ':loc_name' => $location_name_to_save,
                ':lat' => $latitude,
                ':lng' => $longitude,
                ':img' => $image_path,
                ':secret' => $secret_identifier,
                ':date_occ' => $date_occurred,
                ':id' => $item_id,
                ':uid' => $user_id
            ];

            if ($stmt->execute($params)) {
                $msg = "Item updated successfully!";
                $msg_type = "success";
                // Refresh item data
                $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
                $stmt->execute([$item_id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                // Re-parse location for display
                $current_main_location = $item['location_name'];
                $current_location_details = "";
                if (preg_match('/^(.*?) \((.*)\)$/', $item['location_name'], $matches)) {
                    if (array_key_exists($matches[1], $locations_data)) {
                        $current_main_location = $matches[1];
                        $current_location_details = $matches[2];
                    }
                }
            } else {
                $msg = "Database Error: Could not update item.";
                $msg_type = "error";
            }
        } catch (PDOException $e) {
            $msg = "Error: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - UUM Find</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Custom CSS -->
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
    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 0.75rem;
            z-index: 1;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <section class="py-10">
        <div class="max-w-3xl mx-auto px-4">

            <div class="mb-6">
                <a href="my-items.php" class="text-gray-500 hover:text-uum-green flex items-center gap-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Back to My Items
                </a>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Item Details</h1>
                <p class="text-gray-600 dark:text-gray-400">Update the information for your post.</p>
            </div>

            <?php if ($msg != ""): ?>
                <div class="mb-6 p-4 rounded-xl flex items-center <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                    <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-3 text-xl"></i>
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700">

                <!-- Status Toggle -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Item Type</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="lost" class="peer sr-only" <?php echo ($item['status'] == 'lost') ? 'checked' : ''; ?>>
                            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 text-center transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-search text-2xl mb-2 text-red-500"></i>
                                <div class="font-bold text-gray-700 dark:text-gray-200">Lost Item</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="found" class="peer sr-only" <?php echo ($item['status'] == 'found') ? 'checked' : ''; ?>>
                            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 text-center transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-hand-holding-heart text-2xl mb-2 text-green-500"></i>
                                <div class="font-bold text-gray-700 dark:text-gray-200">Found Item</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Title & Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Name</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Occurred</label>
                        <input type="date" name="date_occurred" value="<?php echo date('Y-m-d', strtotime($item['date_occurred'])); ?>" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                    </div>
                </div>

                <!-- Category & Location -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select name="category" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                            <?php foreach ($categories_list as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo ($item['category'] == $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location Name</label>

                        <!-- 1. The Dropdown -->
                        <select name="location_name" id="location_input" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                            <option value="">Select Location</option>
                            <?php foreach ($locations_data as $name => $coords): ?>
                                <option value="<?php echo htmlspecialchars($name); ?>"
                                    <?php echo ($current_main_location == $name) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- 2. The Details Input -->
                        <input type="text" name="location_details"
                            value="<?php echo htmlspecialchars($current_location_details); ?>"
                            placeholder="Specific spot? (e.g. Level 2, Room 101)"
                            class="w-full mt-3 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green text-sm">
                    </div>
                </div>

                <!-- Map -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Update Location Pin</label>
                    <div id="map"></div>
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo $item['latitude']; ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo $item['longitude']; ?>">
                </div>

                <!-- Description & Secret -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="4" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green"><?php echo htmlspecialchars($item['description']); ?></textarea>

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Secret Identifier</label>
                    <input type="text" name="secret_identifier" value="<?php echo htmlspecialchars($item['secret_identifier']); ?>" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                </div>

                <!-- Image Upload -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Update Photo (Leave blank to keep current)</label>

                    <?php if (!empty($item['image_path'])): ?>
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 mb-1">Current Image:</p>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Current Item" class="h-32 w-auto rounded-lg object-cover border border-gray-200">
                        </div>
                    <?php endif; ?>

                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-uum-green transition-colors">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <input type="file" name="item_image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-uum-green hover:file:bg-green-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                    </div>
                </div>

                <!-- Submit -->
                <input type="hidden" name="update_item_btn" value="1">
                <button type="submit" class="w-full bg-uum-green hover:bg-uum-blue text-white font-bold py-4 rounded-xl shadow-lg transform transition-all hover:scale-[1.02]">
                    Update Item
                </button>

            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="js/theme.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. INITIALIZE MAP
            // Get initial values or default to UUM center
            const lat = document.getElementById('latitude').value || 6.460195;
            const lng = document.getElementById('longitude').value || 100.505501;

            const map = L.map('map').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Add Draggable Marker
            const marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            // Update inputs when marker is dragged manually
            marker.on('dragend', function(event) {
                const pos = marker.getLatLng();
                document.getElementById('latitude').value = pos.lat;
                document.getElementById('longitude').value = pos.lng;
            });

            // Move marker on map click
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
            });

            // 2. LOCATION DROPDOWN LOGIC (Now inside the same function scope!)
            const locationSelect = document.getElementById('location_input');
            // Pass PHP array to JS
            const locationsDB = <?php echo json_encode($locations_data); ?>;

            if (locationSelect) {
                locationSelect.addEventListener('change', function() {
                    const selected = this.value;

                    // Check if the selected location exists in our database
                    if (selected && locationsDB[selected]) {
                        const newLat = locationsDB[selected].lat;
                        const newLng = locationsDB[selected].lng;

                        // Move marker and map view
                        const newLatLng = new L.LatLng(newLat, newLng);
                        marker.setLatLng(newLatLng);
                        map.setView(newLatLng, 16);

                        // Update hidden inputs
                        document.getElementById('latitude').value = newLat;
                        document.getElementById('longitude').value = newLng;
                    }
                });
            }
        });
    </script>
</body>

</html>