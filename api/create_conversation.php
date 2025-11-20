<?php
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../start_chat.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$receiver_id = $_POST['receiver_id'];

try {
    // Check if conversation already exists
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Redirect to existing conversation
        header('Location: ../chat.php?conversation=' . $existing['id']);
        exit;
    }

    // Create new conversation
    $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $receiver_id]);
    $conversation_id = $pdo->lastInsertId();

    header('Location: ../chat.php?conversation=' . $conversation_id);
    exit;

} catch (Exception $e) {
    // Handle error
    header('Location: ../start_chat.php?error=1');
    exit;
}
?>