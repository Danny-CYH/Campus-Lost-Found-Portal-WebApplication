<?php
// api/items.php
require_once '../includes/config.php';
// Check if functions file exists before including
if (file_exists('../includes/functions.php')) {
    require_once '../includes/functions.php';
}
// Include points system
if (file_exists('../includes/points_system.php')) {
    require_once '../includes/points_system.php';
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
        // Start transaction
        $pdo->beginTransaction();

        // Check if user owns the item and it's a found item
        $stmt = $pdo->prepare("SELECT user_id, status, title, points_awarded FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            exit;
        }

        // Check if user owns the item
        if ($item['user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
            exit;
        }

        // Check if it's a found item (only found items should get points when returned)
        if ($item['status'] !== 'found') {
            // For lost items, just mark as returned without points
            $stmt = $pdo->prepare("UPDATE items SET is_returned = 1 WHERE id = ?");
            $stmt->execute([$item_id]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Item marked as returned.']);
            exit;
        }

        // Check if points already awarded
        if ($item['points_awarded']) {
            echo json_encode(['success' => false, 'message' => 'Points already awarded for this item.']);
            exit;
        }

        // Mark item as returned
        $stmt = $pdo->prepare("UPDATE items SET is_returned = 1 WHERE id = ?");
        $stmt->execute([$item_id]);

        // Award points for returning a found item
        $pointsResult = $pointsSystem->awardItemReturnPoints($item_id, $user_id);

        if (!$pointsResult['success']) {
            throw new Exception($pointsResult['error']);
        }

        $pdo->commit();

        $response = [
            'success' => true,
            'message' => 'Item marked as returned! You earned 50 points!'
        ];

        // Add badge info if earned new badges
        if (isset($pointsResult['new_badges']) && !empty($pointsResult['new_badges'])) {
            $response['new_badges'] = $pointsResult['new_badges'];
        }

        echo json_encode($response);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Mark Returned Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
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
?>