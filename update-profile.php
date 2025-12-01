<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = ""; // To store success/error messages

// --- PART A: HANDLE FORM SUBMISSION (UPDATE) ---
if (isset($_POST['update_profile_btn'])) {
    
    // 1. Sanitize inputs
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    
    // 2. Prepare SQL Query
    // We update username, email, and the updated_at timestamp
    $sql = "UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?";
    
    if ($stmt = $con->prepare($sql)) {
        // "ssi" means: String, String, Integer
        $stmt->bind_param("ssi", $username, $email, $user_id);
        
        if ($stmt->execute()) {
            $message = "<div class='alert success'>Profile updated successfully!</div>";
        } else {
            $message = "<div class='alert error'>Error updating profile: " . $con->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert error'>Database error: " . $con->error . "</div>";
    }
}

// --- PART B: FETCH CURRENT USER DATA ---
// We need to fetch data *after* the update so the form shows the new info immediately
$sql_fetch = "SELECT * FROM users WHERE id = ?";
if ($stmt_fetch = $con->prepare($sql_fetch)) {
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $user = $result->fetch_assoc();
    $stmt_fetch->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        /* Simple styling to make it look decent immediately */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; padding-top: 50px; }
        .profile-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .readonly-field { background-color: #e9ecef; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="profile-card">
        <h2>My Profile</h2>
        
        <?php echo $message; ?>

        <form action="" method="POST">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" class="readonly-field" readonly>
            </div>

            <div class="form-group">
                <label>Last Login</label>
                <input type="text" value="<?php echo htmlspecialchars($user['last_login']); ?>" class="readonly-field" readonly>
            </div>

            <button type="submit" name="update_profile_btn">Save Changes</button>
            
            <p style="text-align: center; margin-top: 15px;">
                <a href="index.php" style="color: #666;">Back to Home</a>
            </p>
        </form>
    </div>

</body>
</html>