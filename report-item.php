<?php
// 1. DATABASE CONNECTION
include 'includes/config.php';

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

// 3. DEFINE DROPDOWN OPTIONS
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

// Coordinates for auto-pinning feature (Changlun/UUM area)
// YOUR UPDATED COORDINATES
$locations_data = [
    "DKG 1" => ["lat" => 6.466039027050011, "lng" => 100.5078677492091],
    "DKG 2" => ["lat" => 6.4647742594628586, "lng" => 100.50798330995569],
    "DKG 3" => ["lat" => 6.4661107426425515, "lng" => 100.50581076976913],
    "DKG 4" => ["lat" => 6.467700622959108, "lng" => 100.50795079373322],
    "DKG 5" => ["lat" => 6.4586947069409035, "lng" => 100.50587896329803],
    "DKG 6" => ["lat" => 6.45559717337014, "lng" => 100.50464284022831],
    "DKG 7" => ["lat" => 6.4538447845452, "lng" =>  100.4995698090767],
    "DKG 8" => ["lat" => 6.455723956980103, "lng" =>  100.4986244207178],
    "Laluan A" => ["lat" => 6.458164107181955, "lng" => 100.50156375376041],
    "Laluan B" => ["lat" => 6.462486396849043, "lng" => 100.50405586680641],
    "Laluan C" => ["lat" => 6.480566556610062, "lng" => 100.50843323185404],
    "Laluan D" => ["lat" => 6.438846176083953, "lng" => 100.53073822003874],
    "Main Library" => ["lat" => 6.463514704776473, "lng" => 100.50537757514739],
    "Masjid" => ["lat" => 6.463181232234549, "lng" => 100.49870978461347],
    "Pusat Sukan" => ["lat" => 6.474716539969105, "lng" => 100.50443945004015],
    "Varsity Mall" => ["lat" => 6.46288196027126, "lng" => 100.50094032818744],
];

// 4. HANDLE FORM SUBMISSION
if (isset($_POST['report_item_btn'])) {

    // Inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $date_occurred = $_POST['date_occurred'];
    $status = $_POST['status'];
    $secret_identifier = trim($_POST['secret_identifier']);

    // --- LOCATION LOGIC ---
    $main_location = trim($_POST['location_name']); // e.g., "DKG 1"
    $specific_spot = trim($_POST['location_details']); // e.g., "Room 101"

    // Combine them for the database: "DKG 1 (Room 101)"
    if (!empty($specific_spot)) {
        $location_name_to_save = $main_location . " (" . $specific_spot . ")";
    } else {
        $location_name_to_save = $main_location;
    }

    // Coordinates
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    // --- HANDLE IMAGE UPLOAD ---
    $image_path = "";

    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $target_dir = "uploads/items/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["item_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $msg = "Failed to upload image.";
                $msg_type = "error";
            }
        } else {
            $msg = "Only JPG, JPEG, PNG & GIF files allowed.";
            $msg_type = "error";
        }
    }

    // --- INSERT INTO DATABASE ---
    if ($msg == "") {
        try {
            $sql = "INSERT INTO items (
                        user_id, title, description, category, status, location_name, 
                        latitude, longitude, image_path, secret_identifier, 
                        date_occurred, created_at, date_reported 
                    ) VALUES (
                        :uid, :title, :desc, :cat, :status, :loc_name, 
                        :lat, :lng, :img, :secret, 
                        :date_occ, NOW(), NOW()
                    )";

            $stmt = $pdo->prepare($sql);

            $params = [
                ':uid' => $user_id,
                ':title' => $title,
                ':desc' => $description,
                ':cat' => $category,
                ':status' => $status,
                ':loc_name' => $location_name_to_save, // Use the combined name
                ':lat' => $latitude,
                ':lng' => $longitude,
                ':img' => $image_path,
                ':secret' => $secret_identifier,
                ':date_occ' => $date_occurred
            ];

            if ($stmt->execute($params)) {
                $msg = "Item reported successfully!";
                $msg_type = "success";
            } else {
                $msg = "Database Error: Could not save item.";
                $msg_type = "error";
            }
        } catch (PDOException $e) {
            $msg = "Database Error: " . $e->getMessage();
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
    <title>Report Item - UUM Find</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Leaflet CSS (Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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

    <!-- NAVIGATION BAR -->
    <nav class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-search-location text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-uum-green dark:text-uum-gold">UUM Find</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found Portal</p>
                        </div>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Home</a>
                    <a href="lost-items.php" class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Lost Items</a>
                    <a href="found-items.php" class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Found Items</a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300">
                        <i class="fas fa-moon text-gray-600 dark:text-uum-gold text-lg" id="theme-icon"></i>
                    </button>

                    <div class="relative group">
                        <button class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <div class="w-8 h-8 bg-uum-green rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                            <span class="text-gray-700 dark:text-gray-300 hidden lg:block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <a href="dashboard.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-t-xl">
                                <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                            </a>
                            <a href="my-items.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-box mr-3"></i>My Items
                            </a>
                            <a href="update-profile.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-user-cog mr-3"></i>Update Profile
                            </a>
                            <a href="auth/logout.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-b-xl border-t border-gray-200 dark:border-gray-700">
                                <i class="fas fa-sign-out-alt mr-3"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- FORM SECTION -->
    <section class="py-10">
        <div class="max-w-3xl mx-auto px-4">

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Report an Item</h1>
                <p class="text-gray-600 dark:text-gray-400">Did you lose something or find something? Fill out the details below.</p>
            </div>

            <?php if ($msg != ""): ?>
                <div class="mb-6 p-4 rounded-xl flex items-center <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                    <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-3 text-xl"></i>
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endif; ?>

            <form id="reportForm" action="" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700" novalidate>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">I am reporting a...</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="Lost" class="peer sr-only" required>
                            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 text-center transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-search text-2xl mb-2 text-red-500"></i>
                                <div class="font-bold text-gray-700 dark:text-gray-200">Lost Item</div>
                                <div class="text-xs text-gray-500">I lost something</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="Found" class="peer sr-only">
                            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 text-center transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-hand-holding-heart text-2xl mb-2 text-green-500"></i>
                                <div class="font-bold text-gray-700 dark:text-gray-200">Found Item</div>
                                <div class="text-xs text-gray-500">I found something</div>
                            </div>
                        </label>
                    </div>
                    <p id="error-status" class="text-red-500 text-xs mt-2 font-semibold hidden"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Name (Title)</label>
                        <input type="text" name="title" placeholder="e.g. Blue Wallet" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                        <p id="error-title" class="text-red-500 text-xs mt-1 font-semibold hidden"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Occurred</label>
                        <input type="date" name="date_occurred" required max="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                        <p id="error-date" class="text-red-500 text-xs mt-1 font-semibold hidden"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select name="category" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                            <option value="">Select Category</option>
                            <?php foreach ($categories_list as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p id="error-category" class="text-red-500 text-xs mt-1 font-semibold hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location Name</label>

                        <select name="location_name" id="location_input" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                            <option value="">Select Location</option>
                            <?php foreach ($locations_data as $name => $coords): ?>
                                <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p id="error-location" class="text-red-500 text-xs mt-1 font-semibold hidden"></p>

                        <input type="text" name="location_details" placeholder="Specific spot? (e.g. Level 2, Room 101)"
                            class="w-full mt-3 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green text-sm">

                        <div id="location-feedback" class="text-xs mt-1 h-4"></div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pin Exact Location (Map)</label>
                    <div id="map"></div>
                    <p class="text-xs text-gray-500 mt-2">Drag the marker to pinpoint the exact spot.</p>

                    <p id="error-map" class="text-red-500 text-xs mt-1 font-semibold hidden"></p>

                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="4" placeholder="Describe the item (color, brand, specific marks)..." required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green mb-1"></textarea>
                    <p id="error-description" class="text-red-500 text-xs mb-4 font-semibold hidden"></p>

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Secret Identifier</label>
                    <input type="text" name="secret_identifier" placeholder="e.g. Serial number, scratch on back, name inside cover" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green">
                    <p id="error-secret" class="text-red-500 text-xs mt-1 font-semibold hidden"></p>
                    <p class="text-xs text-gray-500 mt-1">This helps prove ownership if multiple people claim it.</p>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload Photo</label>
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-uum-green transition-colors">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <input type="file" name="item_image" accept="image/*" class="block w-full text-sm text-gray-500
            file:mr-4 file:py-2 file:px-4
            file:rounded-full file:border-0
            file:text-sm file:font-semibold
            file:bg-green-50 file:text-uum-green
            hover:file:bg-green-100
            dark:file:bg-gray-700 dark:file:text-gray-300
        ">
                        <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF up to 5MB</p>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <input type="hidden" name="report_item_btn" value="1">

                <button type="submit" class="w-full bg-uum-green hover:bg-uum-blue text-white font-bold py-4 rounded-xl shadow-lg transform transition-all hover:scale-[1.02]">
                    Submit Report
                </button>

            </form>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-800 text-white py-8 md:py-12 mt-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
            <p>&copy; 2025 Universiti Utara Malaysia - Lost & Found Portal. All rights reserved.</p>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script src="js/theme.js"></script>
    <script src="js/mobile-menu.js"></script>

    <!-- Leaflet JS (Map Logic) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- PASS PHP DATA TO JS -->
    <script>
        // Pass the PHP array to a JS variable for the external script to use
        const locationsDB = <?php echo json_encode($locations_data); ?>;
    </script>

    <!-- Main JS logic (External File) -->
    <script src="js/report-item.js"></script>
</body>

</html>