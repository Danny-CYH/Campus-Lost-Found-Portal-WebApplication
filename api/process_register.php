<?php
session_start();
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/register.php');
    exit;
}

// Collect and validate form data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$user_type = $_POST['user_type'];
$agree_terms = isset($_POST['agree_terms']);

$errors = [];

// Validation
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

if (!$agree_terms) {
    $errors[] = 'You must agree to the terms and conditions.';
}

// Check if username or email already exists
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

// If errors, redirect back to form
if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['form_data'] = [
        'username' => $username,
        'email' => $email
    ];
    header('Location: ../auth/register.php');
    exit;
}

// Create user account
try {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(32));

    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, user_type, verification_token, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([$username, $email, $hashed_password, $user_type, $verification_token]);
    $user_id = $pdo->lastInsertId();

    // Send verification email
    $email_sent = sendVerificationEmail($email, $username, $verification_token);

    if ($email_sent) {
        $_SESSION['registration_success'] = "Account created successfully! Please check your email to verify your account.";
    } else {
        $_SESSION['registration_success'] = "Account created successfully! However, we couldn't send the verification email. You can still login and request a new verification email later.";
    }

    $_SESSION['registered_email'] = $email;
    $_SESSION['registered_username'] = $username;

    header('Location: ../api/registration_success.php');
    exit;

} catch (PDOException $e) {
    $errors[] = 'Registration failed: ' . $e->getMessage();
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['form_data'] = ['username' => $username, 'email' => $email];
    header('Location: ../auth/register.php');
    exit;
}
?>