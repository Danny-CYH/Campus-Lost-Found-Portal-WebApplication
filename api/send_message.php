<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$conversation_id = $input['conversation_id'] ?? '';
$receiver_id = $input['receiver_id'] ?? '';

// Validate input
if (empty($message) || empty($conversation_id) || empty($receiver_id)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Verify conversation and user access
    $verify_stmt = $pdo->prepare("SELECT id, user1_id, user2_id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
    $verify_stmt->execute([$conversation_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    $conversation = $verify_stmt->fetch();

    if (!$conversation) {
        echo json_encode(['success' => false, 'error' => 'Invalid conversation']);
        exit;
    }

    // Insert message
    $insert_stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, created_at, is_read) VALUES (?, ?, ?, ?, NOW(), 0)");
    $insert_result = $insert_stmt->execute([$conversation_id, $_SESSION['user_id'], $receiver_id, $message]);

    if (!$insert_result) {
        throw new Exception("Failed to insert message");
    }

    $message_id = $pdo->lastInsertId();

    // Get message data
    $select_stmt = $pdo->prepare("SELECT m.*, u.username as sender_username FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.id = ?");
    $select_stmt->execute([$message_id]);
    $message_data = $select_stmt->fetch();

    // Format Pusher data
    $pusher_data = [
        'id' => $message_data['id'],
        'conversation_id' => $message_data['conversation_id'],
        'sender_id' => $message_data['sender_id'],
        'receiver_id' => $message_data['receiver_id'],
        'sender_username' => $message_data['sender_username'],
        'sender_initials' => strtoupper(substr($message_data['sender_username'], 0, 1)),
        'message' => $message_data['message'],
        'timestamp' => date('g:i A', strtotime($message_data['created_at'])),
        'created_at' => $message_data['created_at'],
        'is_read' => $message_data['is_read']
    ];

    // This will now work correctly - data is passed as array
    $pusher_success = triggerPusher('private-chat-' . $conversation_id, 'new-message', $pusher_data);

    error_log("Pusher trigger result: " . ($pusher_success ? 'SUCCESS' : 'FAILED'));

    echo json_encode([
        'success' => true,
        'message_id' => $message_id,
        'message_data' => $pusher_data,
        'pusher_triggered' => $pusher_success,
        'debug' => [
            'conversation_id' => $conversation_id,
            'channel' => 'chat-' . $conversation_id
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in send_message.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>