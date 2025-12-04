<?php
// 1. DATABASE CONNECTION
include 'includes/config.php';
include 'includes/header.php';

// Check session
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

// 3. FETCH CURRENT USER DATA
try {
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// 4. HANDLE FORM SUBMISSION
if (isset($_POST['update_profile'])) {
    // Collect Input
    $gender = $_POST['gender'];
    $school = $_POST['school'];
    
    // Default: Keep the old image
    $profile_image = $user['profile_image']; 

    // Handle Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['profile_image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed)) {
            if ($_FILES['profile_image']['size'] <= 5000000) { // 5MB
                $clean_username = preg_replace('/[^a-zA-Z0-9]/', '', $user['username']);
                $new_filename = time() . '_' . $clean_username . '.' . $file_ext;
                $upload_dir = 'uploads/profile_images/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $new_filename)) {
                    $profile_image = $new_filename;
                } else {
                    $msg = "Failed to upload image.";
                    $msg_type = "error";
                }
            } else {
                $msg = "Image size must be less than 5MB.";
                $msg_type = "error";
            }
        } else {
            $msg = "Invalid file type. Only JPG, PNG, WEBP.";
            $msg_type = "error";
        }
    }

    // Update Database
    if (empty($msg)) {
        try {
            // Only update Editable Fields
            $sql = "UPDATE users SET gender = :gender, school = :school, profile_image = :profile_image, updated_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([
                ':gender' => $gender, 
                ':school' => $school, 
                ':profile_image' => $profile_image, 
                ':id' => $user_id
            ])) {
                $msg = "Profile updated successfully!";
                $msg_type = "success";
                
                // Refresh data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

            } else {
                $msg = "Something went wrong. Please try again.";
                $msg_type = "error";
            }
        } catch (PDOException $e) {
            $msg = "Database Error: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

// 5. HANDLE REMOVE IMAGE
if (isset($_POST['remove_image'])) {
    $current_image = $user['profile_image'];
    
    // Only proceed if it's not already the default
    if ($current_image !== 'default_avatar.png') {
        try {
            // 1. Update Database to default
            $sql = "UPDATE users SET profile_image = 'default_avatar.png' WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $user_id]);

            // 2. Delete the old file from server (Optional: saves space)
            $file_path = "uploads/profile_images/" . $current_image;
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            $msg = "Profile photo removed.";
            $msg_type = "success";

            // Refresh user data immediately
            $user['profile_image'] = 'default_avatar.png';
            
        } catch (PDOException $e) {
            $msg = "Error removing image: " . $e->getMessage();
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
    <title>Update Profile - UUM Find</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#eff6ff', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' },
                        uum: { green: '#006837', gold: '#FFD700', blue: '#0056b3' }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <section class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center md:text-left">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Profile <span class="text-uum-green dark:text-uum-gold">Settings</span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Update your personal information.</p>
            </div>

            <?php if ($msg != ""): ?>
                <div class="mb-6 p-4 rounded-xl flex items-center <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                    <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-3 text-xl"></i>
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="bg-gradient-to-r from-uum-green to-uum-blue p-6 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <img src="uploads/profile_images/<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'default_avatar.png'; ?>" 
                                 alt="Profile" 
                                 class="w-16 h-16 rounded-full object-cover border-4 border-white/30 bg-white/10 backdrop-blur-sm">
                        </div>
                        <div>
                            <h2 class="text-xl font-bold"><?php echo htmlspecialchars($user['username'] ?? ''); ?></h2>
                            <p class="text-green-100 text-sm"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>

                 <div class="p-6 md:p-8">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 gap-6">

                            <div class="border-b border-gray-100 dark:border-gray-700 pb-6">

                                <div class="flex justify-between items-center mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-camera mr-2 text-uum-green"></i> Update Profile Photo
                                    </label>

                                    <?php if (!empty($user['profile_image']) && $user['profile_image'] !== 'default_avatar.png'): ?>
                                        <button type="submit" name="remove_image"
                                            onclick="return confirm('Are you sure you want to remove your profile photo?')"
                                            class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 flex items-center bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded-lg transition-colors border border-red-100 dark:border-red-900/30">
                                            <i class="fas fa-trash-alt mr-1.5"></i> Remove Photo
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center space-x-6">
                                    <img id="preview_img"
                                        src="uploads/profile_images/<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'default_avatar.png'; ?>"
                                        class="h-24 w-24 rounded-full object-cover border-4 border-gray-100 dark:border-gray-700 shadow-md">

                                    <div class="w-full">
                                        <input type="file" name="profile_image" accept="image/*" onchange="loadFile(event)"
                                            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-uum-green/10 file:text-uum-green hover:file:bg-uum-green/20 cursor-pointer bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl">
                                        <p class="text-xs text-gray-500 mt-2">Recommended: Square JPG, PNG. Max 5MB.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    <i class="fas fa-venus-mars mr-2 text-uum-green"></i> Gender
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative flex cursor-pointer">
                                        <input type="radio" name="gender" value="male" class="sr-only peer" 
                                            <?php echo ($user['gender'] == 'male') ? 'checked' : ''; ?>>
                                        <div class="flex-1 text-center py-3 px-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl peer-checked:border-uum-green peer-checked:bg-uum-green/5 transition-all duration-200">
                                            <i class="fas fa-mars text-blue-500 text-lg mb-2"></i>
                                            <div class="font-medium text-gray-700 dark:text-gray-300">Male</div>
                                        </div>
                                    </label>
                                    <label class="relative flex cursor-pointer">
                                        <input type="radio" name="gender" value="female" class="sr-only peer"
                                            <?php echo ($user['gender'] == 'female') ? 'checked' : ''; ?>>
                                        <div class="flex-1 text-center py-3 px-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl peer-checked:border-uum-green peer-checked:bg-uum-green/5 transition-all duration-200">
                                            <i class="fas fa-venus text-pink-500 text-lg mb-2"></i>
                                            <div class="font-medium text-gray-700 dark:text-gray-300">Female</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-university mr-2 text-uum-green"></i> Academic School
                                </label>
                                <div class="relative">
                                    <select name="school" class="w-full pl-4 pr-10 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uum-green appearance-none">
                                        <?php
                                        $schools = [
                                            "Tunku Puteri Intan Safinaz School of Accountancy", "School of Government", "School of Quantitative Sciences", 
                                            "School of Business Management", "School of International Studies", "School of Applied Psychology, Social Work & Policy", 
                                            "School of Computing", "Islamic Business School", "School of Technology Management & Logistics", 
                                            "School of Creative Industry Management & Performing Arts", "School of Economics, Finance & Banking", 
                                            "School of Education & Modern Languages", "School of Languages, Civilisation & Philosophy", "School of Law", 
                                            "School of Multimedia Technology & Communication", "School of Tourism, Hospitality & Event Management", "National Golf Academy"
                                        ];
                                        foreach ($schools as $s) {
                                            $selected = ($user['school'] === $s) ? 'selected' : '';
                                            echo "<option value=\"$s\" $selected>$s</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 md:p-6 border border-gray-100 dark:border-gray-600/50 mt-4">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-4 uppercase tracking-wider">Account Details (Cannot Edit)</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Username</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Email Address</label>
                                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Account Role</label>
                                        <input type="text" value="<?php echo ucfirst(htmlspecialchars($user['role'] ?? 'User')); ?>" disabled
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Joined Date</label>
                                        <input type="text" value="<?php echo isset($user['created_at']) ? date('d F Y', strtotime($user['created_at'])) : '-'; ?>" disabled
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4 pt-4">
                                <a href="index.php" class="px-6 py-3 text-gray-700 dark:text-gray-300 font-medium hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</a>
                                <button type="submit" name="update_profile"
                                    class="bg-uum-green hover:bg-uum-blue text-white px-8 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-8 md:py-12 mt-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
            <p>&copy; 2025 Universiti Utara Malaysia - Lost & Found Portal. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/theme.js"></script>
    <script src="js/mobile-menu.js"></script>
    
    <script>
        function loadFile(event) {
            var input = event.target;
            var output = document.getElementById('preview_img');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    output.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>