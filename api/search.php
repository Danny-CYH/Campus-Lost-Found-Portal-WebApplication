<?php
// api/search.php
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM items WHERE 1=1";
    $params = [];

    // 1. Search Term
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $term = '%' . $_GET['search'] . '%';
        $sql .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = $term;
        $params[] = $term;
    }

    // 2. Category
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $sql .= " AND category = ?";
        $params[] = $_GET['category'];
    }

    // 3. Status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status = $_GET['status'];
        if ($status === 'returned') {
            $sql .= " AND is_returned = 1";
        } else {
            $sql .= " AND status = ? AND is_returned = 0";
            $params[] = $status;
        }
    }

    // ✅ NEW: Filter by Location
    if (isset($_GET['location']) && !empty($_GET['location'])) {
        // Use partial match (LIKE) so "Library" finds "Main Library"
        $location = '%' . $_GET['location'] . '%';
        $sql .= " AND location_name LIKE ?";
        $params[] = $location;
    }

    // 4. ✅ Time Filter (Updated)
    if (isset($_GET['time']) && !empty($_GET['time'])) {
        $time = $_GET['time'];
        if ($time === 'today') {
            $sql .= " AND DATE(created_at) = CURDATE()";
        } elseif ($time === 'week') {
            // Items from the last 7 days
            $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        } elseif ($time === 'month') {
            // Items from the last 30 days
            $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        }
    }

    // 5. ✅ Sorting (Updated)
    $sort = $_GET['sort'] ?? 'newest'; // Default to newest

    switch ($sort) {
        case 'oldest':
            $sql .= " ORDER BY created_at ASC";
            break;
        case 'occurred_desc':
            // Sort by the date the item was actually lost/found, not when it was posted
            $sql .= " ORDER BY date_occurred DESC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY created_at DESC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($items);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>