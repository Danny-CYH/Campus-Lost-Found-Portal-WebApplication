<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? '';

if (empty($receiver_id)) {
    echo json_encode(['success' => false, 'error' => 'Receiver ID required']);
    exit;
}

// Check if conversation already exists
$stmt = $pdo->prepare("
    SELECT id FROM conversations 
    WHERE (user1_id = ? AND user2_id = ?) 
       OR (user1_id = ? AND user2_id = ?)
    LIMIT 1
");
$stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
$existing_conversation = $stmt->fetch();

if ($existing_conversation) {
    echo json_encode([
        'success' => true,
        'conversation_id' => $existing_conversation['id'],
        'exists' => true
    ]);
} else {
    // Create new conversation
    $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $receiver_id]);
    $conversation_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id,
        'exists' => false
    ]);
}
?>