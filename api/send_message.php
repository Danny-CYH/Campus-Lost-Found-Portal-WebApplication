<?php
// send_message.php
require_once '../includes/config.php';

// Start session at the very beginning
session_start();

// Set headers FIRST
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);

// Log for debugging (remove in production)
error_log('Received data: ' . print_r($input, true));

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit();
}

// Validate required fields
$required_fields = ['message', 'conversation_id', 'receiver_id'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit();
    }
}

// Sanitize and validate
$message = trim($input['message']);
$conversation_id = intval($input['conversation_id']);
$receiver_id = intval($input['receiver_id']);
$sender_id = intval($_SESSION['user_id']);

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

if ($conversation_id <= 0 || $receiver_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid conversation or receiver ID']);
    exit();
}

try {
    // Begin transaction for data consistency
    $pdo->beginTransaction();

    // Save message to database
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, created_at, is_read) 
                           VALUES (?, ?, ?, ?, NOW(), 0)");
    $stmt->execute([$conversation_id, $sender_id, $receiver_id, $message]);
    $message_id = $pdo->lastInsertId();

    // Get sender info
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$sender_id]);
    $sender = $stmt->fetch();

    if (!$sender) {
        throw new Exception('Sender not found');
    }

    // Prepare data for Pusher
    $messageData = [
        'message_id' => $message_id,
        'conversation_id' => $conversation_id,
        'sender_id' => $sender_id,
        'sender_name' => $sender['username'],
        'receiver_id' => $receiver_id,
        'message' => $message,
        'created_at' => date('Y-m-d H:i:s'),
        'is_self_sent' => false // Default for receiver
    ];

    // Trigger to receiver's channel
    if (isset($pusher)) {
        try {
            // Trigger to conversation channel (BOTH users will receive this)
            triggerPusher('private-chat-' . $conversation_id, 'new-message', json_encode($messageData));

            error_log("Pusher triggered to conversation channel: chat-$conversation_id");

        } catch (Exception $e) {
            error_log("Pusher error: " . $e->getMessage());
        }
    }

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message_id' => $message_id,
        'data' => $messageData
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Send message error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send message: ' . $e->getMessage()
    ]);
}

exit();