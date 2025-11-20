<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$socket_id = $_POST['socket_id'];
$channel_name = $_POST['channel_name'];

// Only authorize private channels
if (strpos($channel_name, 'private-') === 0) {
    // For private channels, verify the user has access to the conversation
    $conversation_id = str_replace('private-chat-', '', $channel_name);

    // Verify user is part of this conversation
    $verify_stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE id = ? AND (user1_id = ? OR user2_id = ?)
    ");
    $verify_stmt->execute([$conversation_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    $conversation = $verify_stmt->fetch();

    if (!$conversation) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Not part of conversation']);
        exit;
    }

    $response = [
        'auth' => PUSHER_KEY . ':' . hash_hmac('sha256', $socket_id . ':' . $channel_name, PUSHER_SECRET)
    ];

    echo json_encode($response);
} else {
    // Reject non-private channels
    http_response_code(403);
    echo json_encode(['error' => 'Only private channels are allowed']);
}
?>