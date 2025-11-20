<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$conversation_id = $_GET['conversation_id'] ?? '';

if (empty($conversation_id)) {
    echo json_encode(['error' => 'Conversation ID required']);
    exit;
}

try {
    // Verify user has access to this conversation
    $verify_stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE id = ? AND (user1_id = ? OR user2_id = ?)
    ");
    $verify_stmt->execute([$conversation_id, $_SESSION['user_id'], $_SESSION['user_id']]);

    if (!$verify_stmt->fetch()) {
        echo json_encode(['error' => 'Access denied to this conversation']);
        exit;
    }

    // Get messages - FIXED: Updated for your table structure
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as sender_username 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.conversation_id = ? 
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$conversation_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format messages for frontend
    $formatted_messages = [];
    foreach ($messages as $message) {
        $formatted_messages[] = [
            'id' => $message['id'],
            'conversation_id' => $message['conversation_id'],
            'sender_id' => $message['sender_id'],
            'receiver_id' => $message['receiver_id'],
            'sender_username' => $message['sender_username'],
            'sender_initials' => strtoupper(substr($message['sender_username'], 0, 1)),
            'message' => $message['message'],
            'timestamp' => date('g:i A', strtotime($message['created_at'])),
            'created_at' => $message['created_at'],
            'is_read' => $message['is_read']
        ];
    }

    echo json_encode($formatted_messages);

} catch (PDOException $e) {
    error_log("Error fetching messages: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch messages: ' . $e->getMessage()]);
}
?>