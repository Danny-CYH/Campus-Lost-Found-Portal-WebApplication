<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/points_system.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit;
}

try {
    switch ($data['action']) {
        case 'mark_returned':
            if (!isset($data['item_id'])) {
                echo json_encode(['success' => false, 'error' => 'Item ID required']);
                exit;
            }

            $result = $pointsSystem->awardItemReturnPoints($data['item_id'], $user_id);
            echo json_encode($result);
            break;

        case 'award_points':
            if (!isset($data['points']) || !isset($data['source'])) {
                echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                exit;
            }

            $result = $pointsSystem->awardPoints(
                $user_id,
                $data['points'],
                $data['source'],
                $data['description'] ?? null,
                $data['item_id'] ?? null
            );
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>