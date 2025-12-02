<?php
// api/items.php
require_once '../includes/config.php';
// Check if functions file exists before including
if (file_exists('../includes/functions.php')) {
    require_once '../includes/functions.php';
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

// 1. MARK RETURNED
if ($action === 'mark_returned' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'] ?? null;

    if (!$item_id) {
        echo json_encode(['success' => false, 'message' => 'Missing item ID.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item || $item['user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
            exit;
        }

        // Check if is_returned column exists first (Safety)
        $stmt = $pdo->prepare("UPDATE items SET is_returned = 1 WHERE id = ?");
        $stmt->execute([$item_id]);

        echo json_encode(['success' => true, 'message' => 'Item marked as returned.']);
    } catch (PDOException $e) {
        error_log("Mark Returned Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 2. DELETE ITEM
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT user_id FROM items WHERE id = ?");
        $check->execute([$id]);
        $item = $check->fetch(PDO::FETCH_ASSOC);

        if ($item && $item['user_id'] == $user_id) {
            $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Item deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database delete failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not own this item.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// 3. UPDATE ITEM
elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $location_name = trim($_POST['location_name'] ?? '');

    if (!$id || !$title || !$category) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT user_id FROM items WHERE id = ?");
        $check->execute([$id]);
        $item = $check->fetch(PDO::FETCH_ASSOC);

        if ($item && $item['user_id'] == $user_id) {
            $sql = "UPDATE items SET title = ?, category = ?, description = ?, location_name = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$title, $category, $description, $location_name, $id])) {
                echo json_encode(['success' => true, 'message' => 'Item updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not own this item.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// 4. INVALID ACTION
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action or request method.']);
    exit;
}
