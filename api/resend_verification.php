<?php
session_start();
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $_SESSION['error'] = "Please enter your email address.";
        header('Location: login.php');
        exit;
    }

    try {
        // Check if user exists and needs verification
        $stmt = $pdo->prepare("
            SELECT id, username, email, verification_token, email_verified 
            FROM users 
            WHERE email = ? AND email_verified = 0
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate new verification token
            $new_token = bin2hex(random_bytes(32));

            // Update token in database
            $update_stmt = $pdo->prepare("
                UPDATE users 
                SET verification_token = ?, created_at = NOW() 
                WHERE id = ?
            ");
            $update_stmt->execute([$new_token, $user['id']]);

            // Send new verification email
            $email_sent = sendVerificationEmail($user['email'], $user['username'], $new_token);

            if ($email_sent) {
                $_SESSION['success'] = "A new verification email has been sent to " . htmlspecialchars($email) . ". Please check your inbox.";
            } else {
                $_SESSION['error'] = "Failed to send verification email. Please try again later.";
            }

        } else {
            $_SESSION['error'] = "No pending verification found for this email address, or email is already verified.";
        }

    } catch (PDOException $e) {
        error_log("Resend verification error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again.";
    }

    header('Location: ../auth/login.php');
    exit;

} else {
    header('Location: ../auth/login.php');
    exit;
}
?>