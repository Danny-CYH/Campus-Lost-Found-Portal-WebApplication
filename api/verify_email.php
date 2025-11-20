<?php
session_start();
include '../includes/config.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error'] = "Invalid verification link. Please make sure you copied the entire link from your email.";
    header('Location: login.php');
    exit;
}

try {
    // Check if token exists and is valid (within 24 hours)
    $stmt = $pdo->prepare("
        SELECT id, username, email, created_at 
        FROM users 
        WHERE verification_token = ? 
        AND email_verified = 0 
        AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify email
        $update_stmt = $pdo->prepare("
            UPDATE users 
            SET email_verified = 1, 
                verification_token = NULL, 
                verified_at = NOW() 
            WHERE id = ?
        ");
        $update_stmt->execute([$user['id']]);

        // Log the verification
        error_log("Email verified for user: " . $user['email'] . " (ID: " . $user['id'] . ")");

        $_SESSION['success'] = "Email verified successfully! You can now login to your account.";
        $_SESSION['verified_email'] = $user['email'];
        $_SESSION['verified_username'] = $user['username'];

    } else {
        // Check if token exists but expired
        $stmt_expired = $pdo->prepare("
            SELECT id FROM users 
            WHERE verification_token = ? 
            AND email_verified = 0 
            AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt_expired->execute([$token]);
        $expired_user = $stmt_expired->fetch();

        if ($expired_user) {
            $_SESSION['error'] = "Verification link has expired. Please request a new verification email.";
        } else {
            $_SESSION['error'] = "Invalid or already used verification token.";
        }
    }

} catch (PDOException $e) {
    error_log("Email verification error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while verifying your email. Please try again or contact support.";
}

header('Location: ../auth/login.php');
exit;
?>