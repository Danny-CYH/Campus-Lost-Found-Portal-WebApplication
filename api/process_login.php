<?php
session_start();
include '../includes/config.php';

// Function to validate login credentials
function validateLogin($pdo, $username, $password)
{
    try {
        // Check if user exists by username or email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, user_type, email_verified, is_active 
            FROM users 
            WHERE (username = ? OR email = ?) AND is_active = 1
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            return [
                'success' => false,
                'message' => 'Please verify your email address before logging in'
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        // Login successful
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'user_type' => $user['user_type']
            ]
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Function to log login attempt
function logLoginAttempt($pdo, $username, $ip_address, $success, $error_message = null, $user_id = null)
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO login_logs (user_id, username, ip_address, success, error_message, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $username, $ip_address, $success ? 1 : 0, $error_message]);
    } catch (PDOException $e) {
        // Silently fail logging
    }
}

// Function to update last login timestamp
function updateLastLogin($pdo, $user_id)
{
    try {
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Function to check for brute force attempts
function checkBruteForce($pdo, $ip_address, $username)
{
    try {
        // Check for failed attempts in the last 30 minutes
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_logs 
            WHERE (ip_address = ? OR username = ?) 
            AND success = 0 
            AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");
        $stmt->execute([$ip_address, $username]);
        $result = $stmt->fetch();

        // If more than 5 failed attempts in 30 minutes, block for 15 minutes
        if ($result['attempts'] >= 5) {
            return [
                'blocked' => true,
                'message' => 'Too many failed login attempts. Please try again in 15 minutes.'
            ];
        }

        return ['blocked' => false];

    } catch (PDOException $e) {
        return ['blocked' => false];
    }
}

// Main login processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Get client IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Initialize errors array
    $errors = [];

    // Validate required fields
    if (empty($username)) {
        $errors[] = "Username or email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // If no basic errors, proceed with validation
    if (empty($errors)) {
        // Check for brute force attempts
        $brute_force_check = checkBruteForce($pdo, $ip_address, $username);
        if ($brute_force_check['blocked']) {
            $errors[] = $brute_force_check['message'];
        } else {
            // Validate login credentials
            $login_result = validateLogin($pdo, $username, $password);

            if ($login_result['success']) {
                // Login successful
                $user = $login_result['user'];

                // Update last login
                updateLastLogin($pdo, $user['id']);

                // Log successful login
                logLoginAttempt($pdo, $username, $ip_address, true, null, $user['id']);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['user_type'];
                $_SESSION['logged_in'] = true;

                // Set remember me cookie if requested (30 days)
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days

                    // Store remember token in database
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO remember_tokens (user_id, token, expires_at) 
                            VALUES (?, ?, FROM_UNIXTIME(?))
                        ");
                        $stmt->execute([$user['id'], $token, $expiry]);

                        // Set cookie
                        setcookie('remember_token', $token, $expiry, '/', '', false, true);
                        setcookie('user_id', $user['id'], $expiry, '/', '', false, true);

                    } catch (PDOException $e) {
                        // Silently fail remember me functionality
                    }
                }

                // Set success message
                $_SESSION['login_success'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";

                // Redirect to dashboard or intended page
                if (isset($_SESSION['redirect_url'])) {
                    $redirect_url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header('Location: ' . $redirect_url);
                } else {
                    header('Location: ../index.php');
                }
                exit;

            } else {
                // Login failed
                $errors[] = $login_result['message'];

                // Log failed attempt
                logLoginAttempt($pdo, $username, $ip_address, false, $login_result['message']);
            }
        }
    }

    // If there are errors, redirect back to login form
    if (!empty($errors)) {
        // Store errors and form data in session
        $_SESSION['login_errors'] = $errors;
        $_SESSION['form_data'] = [
            'username' => $username
        ];

        // Redirect back to login form
        header('Location: ../auth/login.php');
        exit;
    }
} else {
    // If not POST request, redirect to login form
    header('Location: ../auth/login.php');
    exit;
}
?>