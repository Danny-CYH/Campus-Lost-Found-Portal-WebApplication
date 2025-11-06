<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll':
        getAllItems();
        break;
    case 'get':
        getItem();
        break;
    case 'create':
        createItem();
        break;
    case 'markReturned':
        markItemReturned();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getAllItems()
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT i.*, u.username 
        FROM items i 
        JOIN users u ON i.user_id = u.id 
        WHERE i.status != 'returned' 
        ORDER BY i.created_at DESC
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($items);
}

function getItem()
{
    global $pdo;

    $id = $_GET['id'] ?? 0;

    $stmt = $pdo->prepare("
        SELECT i.*, u.username 
        FROM items i 
        JOIN users u ON i.user_id = u.id 
        WHERE i.id = ?
    ");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo json_encode($item);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
}

function createItem()
{
    global $pdo;

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to report an item']);
        return;
    }

    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? 'lost';
    $location_name = $_POST['location_name'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $secret_identifier = $_POST['secret_identifier'] ?? '';
    $date_occurred = $_POST['date_occurred'] ?? date('Y-m-d');

    // Validate required fields
    if (empty($title) || empty($description) || empty($category) || empty($location_name) || empty($secret_identifier)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        return;
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;

        // Check if file is an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            echo json_encode(['success' => false, 'message' => 'File is not an image']);
            return;
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = 'uploads/' . $filename;
        }
    }

    // Insert item into database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO items 
            (user_id, title, description, category, status, location_name, latitude, longitude, image_path, secret_identifier, date_reported, date_occurred) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)
        ");

        $stmt->execute([
            $user_id,
            $title,
            $description,
            $category,
            $status,
            $location_name,
            $latitude,
            $longitude,
            $image_path,
            $secret_identifier,
            $date_occurred
        ]);

        // Log activity
        logActivity($user_id, "Reported $status item: $title", $pdo->lastInsertId());

        echo json_encode(['success' => true, 'message' => 'Item reported successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function markItemReturned()
{
    global $pdo;

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in']);
        return;
    }

    $id = $_POST['id'] ?? 0;
    $user_id = $_SESSION['user_id'];

    // Verify user owns the item
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found or you do not have permission']);
        return;
    }

    // Update item status
    try {
        $stmt = $pdo->prepare("UPDATE items SET status = 'returned' WHERE id = ?");
        $stmt->execute([$id]);

        // Log activity
        logActivity($user_id, "Marked item as returned: {$item['title']}", $id);

        echo json_encode(['success' => true, 'message' => 'Item marked as returned']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>