<?php
// auth/pusher_auth.php - Manual authentication without Pusher SDK
require_once __DIR__ . '/../includes/config.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request for debugging
error_log("=== PUSHER AUTH REQUEST ===");
error_log("Channel: " . ($_POST['channel_name'] ?? 'NOT SET'));
error_log("Socket: " . ($_POST['socket_id'] ?? 'NOT SET'));
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// Check authentication
if (!isset($_SESSION['user_id'])) {
    error_log("❌ User not authenticated");
    http_response_code(401);
    die(json_encode(['error' => 'Not authenticated']));
}

$channel_name = $_POST['channel_name'] ?? '';
$socket_id = $_POST['socket_id'] ?? '';

if (empty($channel_name) || empty($socket_id)) {
    error_log("❌ Missing channel_name or socket_id");
    http_response_code(400);
    die(json_encode(['error' => 'Missing parameters']));
}

// Extract conversation_id from channel name
if (preg_match('/^private-chat-(\d+)$/', $channel_name, $matches)) {
    $conversation_id = $matches[1];
    $user_id = $_SESSION['user_id'];

    error_log("Checking access for user $user_id to conversation $conversation_id");

    try {
        // Check if user is part of this conversation
        $stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
        $stmt->execute([$conversation_id, $user_id, $user_id]);
        $conversation = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $profile_image = $user['profile_image'] ?? '';

        if (!$conversation) {
            error_log("❌ User $user_id is NOT a member of conversation $conversation_id");
            http_response_code(403);
            die(json_encode(['error' => 'Not a member of this conversation']));
        }

        error_log("✅ User $user_id is authorized for conversation $conversation_id");

    } catch (Exception $e) {
        error_log("❌ Database error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Database error']));
    }
} else {
    error_log("❌ Invalid channel name format: $channel_name");
    http_response_code(400);
    die(json_encode(['error' => 'Invalid channel name']));
}

// MANUAL AUTH GENERATION
// Your Pusher credentials from config.php
$app_key = PUSHER_KEY; // Should be '585e3129f2fd92e29c0b'
$app_secret = PUSHER_SECRET; // Should be '3740bcb95806a2bd14ab'

error_log("Using app_key: $app_key");
error_log("Using app_secret: " . substr($app_secret, 0, 4) . "..."); // Don't log full secret

// Generate the signature
$string_to_sign = $socket_id . ':' . $channel_name;
$signature = hash_hmac('sha256', $string_to_sign, $app_secret, false);

error_log("String to sign: $string_to_sign");
error_log("Generated signature: $signature");

// Return the auth response
$auth = [
    'auth' => $app_key . ':' . $signature
];

// If it's a presence channel, add channel_data (not needed for private channels)
if (strpos($channel_name, 'presence-') === 0) {
    $auth['channel_data'] = json_encode([
        'user_id' => $user_id,
        'user_info' => [
            'username' => $_SESSION['username'] ?? 'User',
            'profile_image' => $profile_image
        ]
    ]);
}

error_log("Final auth response: " . json_encode($auth));

header('Content-Type: application/json');
echo json_encode($auth);