<?php
// includes/points_system.php

require_once 'config.php';

class PointsSystem
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Award points to user
    public function awardPoints($user_id, $points, $source, $description = null, $item_id = null)
    {
        try {
            // REMOVED: $this->pdo->beginTransaction();

            // Check if user has points record
            $stmt = $this->pdo->prepare("SELECT id FROM user_points WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $hasRecord = $stmt->fetch();

            if (!$hasRecord) {
                // Create points record
                $stmt = $this->pdo->prepare("INSERT INTO user_points (user_id, points, level) VALUES (?, ?, 1)");
                $stmt->execute([$user_id, $points]);
            } else {
                // Update existing points
                $stmt = $this->pdo->prepare("UPDATE user_points SET points = points + ? WHERE user_id = ?");
                $stmt->execute([$points, $user_id]);
            }

            // Record transaction
            $stmt = $this->pdo->prepare("
            INSERT INTO point_transactions (user_id, points, transaction_type, source, description, item_id) 
            VALUES (?, ?, 'earned', ?, ?, ?)
        ");
            $stmt->execute([$user_id, $points, $source, $description, $item_id]);

            // Update level based on points
            $this->updateUserLevel($user_id);

            // Check for new badges
            $newBadges = $this->checkForNewBadges($user_id);

            // REMOVED: $this->pdo->commit();

            return [
                'success' => true,
                'new_points' => $points,
                'new_badges' => $newBadges
            ];

        } catch (Exception $e) {
            // REMOVED: $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Update user level (every 100 points = 1 level)
    private function updateUserLevel($user_id)
    {
        $stmt = $this->pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user) {
            $newLevel = floor($user['points'] / 100) + 1;
            $stmt = $this->pdo->prepare("UPDATE user_points SET level = ? WHERE user_id = ?");
            $stmt->execute([$newLevel, $user_id]);
        }
    }

    // Check if user earned new badges
    private function checkForNewBadges($user_id)
    {
        // Get user's current points and badges
        $stmt = $this->pdo->prepare("SELECT points, badges FROM user_points WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user)
            return [];

        $currentBadges = json_decode($user['badges'] ?? '[]', true) ?: [];
        $userPoints = $user['points'];

        // Get all badges user doesn't have yet
        $badgeNames = array_column($currentBadges, 'name');
        $badgeCondition = '';

        if (!empty($badgeNames)) {
            $placeholders = implode(',', array_fill(0, count($badgeNames), '?'));
            $badgeCondition = "AND badge_name NOT IN ($placeholders)";
        }

        $sql = "SELECT * FROM user_badges WHERE points_required <= ? $badgeCondition";
        $stmt = $this->pdo->prepare($sql);

        $params = [$userPoints];
        if (!empty($badgeNames)) {
            $params = array_merge($params, $badgeNames);
        }

        $stmt->execute($params);
        $eligibleBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $newBadges = [];
        foreach ($eligibleBadges as $badge) {
            // Add badge to user's badges
            $currentBadges[] = [
                'name' => $badge['badge_name'],
                'icon' => $badge['badge_icon'],
                'earned_at' => date('Y-m-d H:i:s')
            ];

            $newBadges[] = $badge;
        }

        // Update user's badges
        if (!empty($newBadges)) {
            $stmt = $this->pdo->prepare("UPDATE user_points SET badges = ? WHERE user_id = ?");
            $stmt->execute([json_encode($currentBadges), $user_id]);
        }

        return $newBadges;
    }

    // Get user points and stats
    public function getUserStats($user_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT up.points, up.level, up.badges,
                   (SELECT COUNT(*) FROM point_transactions WHERE user_id = ? AND transaction_type = 'earned') as total_transactions,
                   (SELECT COALESCE(SUM(points), 0) FROM point_transactions WHERE user_id = ? AND transaction_type = 'earned') as total_points_earned
            FROM user_points up
            WHERE up.user_id = ?
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stats) {
            // Create record if doesn't exist
            $stmt = $this->pdo->prepare("INSERT INTO user_points (user_id, points, level) VALUES (?, 0, 1)");
            $stmt->execute([$user_id]);

            $stats = [
                'points' => 0,
                'level' => 1,
                'badges' => '[]',
                'total_transactions' => 0,
                'total_points_earned' => 0
            ];
        }

        $stats['badges'] = json_decode($stats['badges'] ?? '[]', true) ?: [];

        return $stats;
    }

    // Get user's point history
    public function getPointHistory($user_id, $limit = 20)
    {
        // Sanitize the limit value
        $limit = (int) $limit;
        if ($limit <= 0)
            $limit = 20;
        if ($limit > 100)
            $limit = 100;

        $stmt = $this->pdo->prepare("
        SELECT pt.*, i.title as item_title
        FROM point_transactions pt
        LEFT JOIN items i ON pt.item_id = i.id
        WHERE pt.user_id = ?
        ORDER BY pt.created_at DESC
        LIMIT " . $limit
        );

        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get points leaderboard
    public function getLeaderboard($limit = 10)
    {
        // Sanitize the limit value
        $limit = (int) $limit;
        if ($limit <= 0)
            $limit = 10;
        if ($limit > 100)
            $limit = 100;

        $stmt = $this->pdo->prepare("
        SELECT 
            u.id, 
            u.username, 
            u.profile_image, 
            COALESCE(up.points, 0) as points, 
            COALESCE(up.level, 1) as level,
            (@rank := @rank + 1) as rank
        FROM users u
        LEFT JOIN user_points up ON u.id = up.user_id
        CROSS JOIN (SELECT @rank := 0) r
        WHERE u.is_active = 1
        ORDER BY up.points DESC
        LIMIT " . $limit
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Award points for item return
    public function awardItemReturnPoints($item_id, $finder_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT user_id, title FROM items WHERE id = ?");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch();

            if (!$item)
                return ['success' => false, 'error' => 'Item not found'];

            // Check if points already awarded
            $stmt = $this->pdo->prepare("SELECT points_awarded, awarded_to FROM items WHERE id = ?");
            $stmt->execute([$item_id]);
            $itemCheck = $stmt->fetch();

            if ($itemCheck && $itemCheck['points_awarded']) {
                return ['success' => false, 'error' => 'Points already awarded to user ID: ' . $itemCheck['awarded_to']];
            }

            // Award 50 points to finder - NO TRANSACTION HERE
            $result = $this->awardPoints(
                $finder_id,
                50,
                'item_return',
                "Returned item: " . $item['title'],
                $item_id
            );

            if ($result['success']) {
                // Mark item as points awarded
                $stmt = $this->pdo->prepare("UPDATE items SET points_awarded = TRUE, awarded_to = ? WHERE id = ?");
                $stmt->execute([$finder_id, $item_id]);
            }

            return $result;

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Initialize points system
$pointsSystem = new PointsSystem($pdo);
?>