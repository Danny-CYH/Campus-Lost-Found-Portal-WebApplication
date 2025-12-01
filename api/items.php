<?php
// api/items.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

if ($action === 'mark_returned' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'] ?? null;

    if (!$item_id) {
        echo json_encode(['success' => false, 'message' => 'Missing item ID.']);
        exit;
    }

    try {
        // 1. Verify item ownership for security
        $stmt = $pdo->prepare("SELECT user_id FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item || $item['user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized action or item not found.']);
            exit;
        }

        // 2. Update the new column: is_returned = 1
        $stmt = $pdo->prepare("UPDATE items SET is_returned = 1 WHERE id = ?");
        $stmt->execute([$item_id]);

        echo json_encode(['success' => true, 'message' => 'Item marked as returned.']);
    } catch (PDOException $e) {
        error_log("Mark Returned Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action or request method.']);
}
