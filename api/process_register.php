<?php
session_start();
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/register.php');
    exit;
}

// 1. Collect and validate form data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$user_type = $_POST['user_type'];
$agree_terms = isset($_POST['agree_terms']);

// New Inputs
$gender = $_POST['gender'] ?? null;
$school = $_POST['school'] ?? null;

$errors = [];

// 2. Validation
if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $errors[] = 'Username must be 3-20 characters and contain only letters, numbers, and underscores.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}

// New Validation
if (empty($gender)) {
    $errors[] = 'Please select your gender.';
}
if (empty($school)) {
    $errors[] = 'Please select your academic school.';
}

if (!$agree_terms) {
    $errors[] = 'You must agree to the terms and conditions.';
}

// 3. Check if username or email already exists
if (empty($errors)) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        }
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

// 4. Handle Image Upload (Only if no previous errors)
$profile_image = 'default_avatar.png'; // Default value

if (empty($errors)) {
    // Check if a file was actually uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['profile_image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed)) {
            if ($_FILES['profile_image']['size'] <= 5000000) { // 5MB limit
                // Create unique filename
                $clean_username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
                $new_filename = time() . '_' . $clean_username . '.' . $file_ext;
                
                $upload_dir = '../uploads/profile_images/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $new_filename)) {
                    $profile_image = $new_filename;
                } else {
                    $errors[] = 'Failed to upload image. Please try again.';
                }
            } else {
                $errors[] = 'Image size must be less than 5MB.';
            }
        } else {
            $errors[] = 'Invalid file type. Only JPG, PNG, and WEBP allowed.';
        }
    }
}

// 5. If errors, redirect back to form
if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['form_data'] = [
        'username' => $username,
        'email' => $email,
        'user_type' => $user_type,
        'gender' => $gender, // Keep selection
        'school' => $school  // Keep selection
    ];
    header('Location: ../auth/register.php');
    exit;
}

// 6. Create user account
try {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(32));

    // UPDATED QUERY: Added gender, school, profile_image
    // Note: email_verified defaults to 0 in database, so we don't need to insert it manually
    $stmt = $pdo->prepare("
        INSERT INTO users (
            username, 
            email, 
            password, 
            user_type, 
            gender, 
            school, 
            profile_image, 
            verification_token, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $username, 
        $email, 
        $hashed_password, 
        $user_type, 
        $gender, 
        $school, 
        $profile_image, 
        $verification_token
    ]);
    
    $user_id = $pdo->lastInsertId();

    // Send verification email
    // Ensure this function uses the correct BASE_URL from config
    if (function_exists('sendVerificationEmail')) {
        $email_sent = sendVerificationEmail($email, $username, $verification_token);

        if ($email_sent) {
            $_SESSION['registration_success'] = "Account created successfully! Please check your email to verify your account.";
        } else {
            $_SESSION['registration_success'] = "Account created successfully! However, we couldn't send the verification email. You can still login and request a new verification email later.";
        }
    } else {
        // Fallback if function is missing or fails
        $_SESSION['registration_success'] = "Account created successfully! Please verify your email.";
    }

    $_SESSION['registered_email'] = $email;
    $_SESSION['registered_username'] = $username;

    header('Location: ../api/registration_success.php');
    exit;

} catch (PDOException $e) {
    $errors[] = 'Registration failed: ' . $e->getMessage();
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['form_data'] = [
        'username' => $username, 
        'email' => $email,
        'gender' => $gender,
        'school' => $school
    ];
    header('Location: ../auth/register.php');
    exit;
}
?>