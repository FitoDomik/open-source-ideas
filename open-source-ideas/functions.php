<?php
require_once 'config.php';
function toggleLike($ideaId, $userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM idea_likes WHERE idea_id = ? AND user_id = ?");
    $stmt->execute([$ideaId, $userId]);
    if ($stmt->fetch()) {
        $stmt = $db->prepare("DELETE FROM idea_likes WHERE idea_id = ? AND user_id = ?");
        $stmt->execute([$ideaId, $userId]);
        return false; 
    } else {
        $stmt = $db->prepare("INSERT INTO idea_likes (idea_id, user_id) VALUES (?, ?)");
        $stmt->execute([$ideaId, $userId]);
        return true; 
    }
}
function getLikesCount($ideaId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM idea_likes WHERE idea_id = ?");
    $stmt->execute([$ideaId]);
    $result = $stmt->fetch();
    return $result['count'];
}
function hasUserLiked($ideaId, $userId) {
    if (!$userId) return false;
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM idea_likes WHERE idea_id = ? AND user_id = ?");
    $stmt->execute([$ideaId, $userId]);
    return $stmt->fetch() !== false;
}
function addComment($ideaId, $userId, $text) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO comments (idea_id, user_id, text) VALUES (?, ?, ?)");
    return $stmt->execute([$ideaId, $userId, $text]);
}
function getComments($ideaId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.*, u.username, u.avatar 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.idea_id = ? 
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$ideaId]);
    return $stmt->fetchAll();
}
function deleteComment($commentId, $userId) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    return $stmt->execute([$commentId, $userId]);
}
function updateIdeaStatus($ideaId, $status, $userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT user_id FROM ideas WHERE id = ?");
    $stmt->execute([$ideaId]);
    $idea = $stmt->fetch();
    if (!$idea || $idea['user_id'] != $userId) {
        return false;
    }
    $allowedStatuses = ['new', 'in_progress', 'completed', 'abandoned'];
    if (!in_array($status, $allowedStatuses)) {
        return false;
    }
    $stmt = $db->prepare("UPDATE ideas SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $ideaId]);
}
function getStatusName($status) {
    $statuses = [
        'new' => 'Новая',
        'in_progress' => 'В разработке',
        'completed' => 'Завершена',
        'abandoned' => 'Заброшена'
    ];
    return $statuses[$status] ?? 'Новая';
}
function getStatusColor($status) {
    $colors = [
        'new' => '#6366f1',
        'in_progress' => '#f59e0b',
        'completed' => '#22c55e',
        'abandoned' => '#64748b'
    ];
    return $colors[$status] ?? '#6366f1';
}
function checkAndAwardBadges($userId) {
    $db = getDB();
    $stats = getUserStats($userId);
    $badges = $db->query("SELECT * FROM badges")->fetchAll();
    foreach ($badges as $badge) {
        $stmt = $db->prepare("SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?");
        $stmt->execute([$userId, $badge['id']]);
        if ($stmt->fetch()) {
            continue; 
        }
        $earned = false;
        switch ($badge['criteria']) {
            case 'ideas_count_1':
                $earned = $stats['ideas_count'] >= 1;
                break;
            case 'ideas_count_5':
                $earned = $stats['ideas_count'] >= 5;
                break;
            case 'ideas_count_10':
                $earned = $stats['ideas_count'] >= 10;
                break;
            case 'responses_count_1':
                $earned = $stats['responses_count'] >= 1;
                break;
            case 'responses_count_5':
                $earned = $stats['responses_count'] >= 5;
                break;
            case 'responses_count_10':
                $earned = $stats['responses_count'] >= 10;
                break;
            case 'likes_received_10':
                $earned = $stats['likes_received'] >= 10;
                break;
            case 'likes_received_50':
                $earned = $stats['likes_received'] >= 50;
                break;
            case 'comments_count_10':
                $earned = $stats['comments_count'] >= 10;
                break;
            case 'completed_idea_1':
                $earned = $stats['completed_ideas'] >= 1;
                break;
        }
        if ($earned) {
            $stmt = $db->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt->execute([$userId, $badge['id']]);
        }
    }
}
function getUserStats($userId) {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM ideas WHERE user_id = $userId) as ideas_count,
            (SELECT COUNT(*) FROM responses WHERE user_id = $userId) as responses_count,
            (SELECT COUNT(*) FROM comments WHERE user_id = $userId) as comments_count,
            (SELECT COUNT(*) FROM idea_likes WHERE idea_id IN (SELECT id FROM ideas WHERE user_id = $userId)) as likes_received,
            (SELECT COUNT(*) FROM ideas WHERE user_id = $userId AND status = 'completed') as completed_ideas
    ");
    return $stmt->fetch();
}
function getUserBadges($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT b.*, ub.earned_at, ub.badge_id 
        FROM user_badges ub 
        JOIN badges b ON ub.badge_id = b.id 
        WHERE ub.user_id = ? 
        ORDER BY ub.earned_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
function getUserAvatar($avatar, $username) {
    if ($avatar) {
        if (file_exists('upload/avatars/' . $avatar)) {
            return 'upload/avatars/' . $avatar;
        } elseif (file_exists('upload/' . $avatar)) {
            return 'upload/' . $avatar;
        }
    }
    $hash = md5(strtolower(trim($username)));
    return "https://www.gravatar.com/avatar/$hash?d=identicon&s=200";
}
function getBadgeProgress($userId, $criteria, $badgeId) {
    $db = getDB();
    if (!$criteria || strpos($criteria, ':') === false) {
        return null;
    }
    list($type, $required) = explode(':', $criteria);
    $current = 0;
    switch ($type) {
        case 'ideas':
            $stmt = $db->prepare("SELECT COUNT(*) FROM ideas WHERE user_id = ?");
            $stmt->execute([$userId]);
            $current = $stmt->fetchColumn();
            break;
        case 'responses':
            $stmt = $db->prepare("SELECT COUNT(*) FROM responses WHERE user_id = ?");
            $stmt->execute([$userId]);
            $current = $stmt->fetchColumn();
            break;
        case 'likes':
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT il.id) 
                FROM idea_likes il
                JOIN ideas i ON il.idea_id = i.id
                WHERE i.user_id = ?
            ");
            $stmt->execute([$userId]);
            $current = $stmt->fetchColumn();
            break;
        case 'comments':
            $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
            $stmt->execute([$userId]);
            $current = $stmt->fetchColumn();
            break;
        case 'profile':
            $stmt = $db->prepare("SELECT bio, avatar, website FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $filled = 0;
            if ($user['bio']) $filled++;
            if ($user['avatar']) $filled++;
            if ($user['website']) $filled++;
            $current = $filled;
            $required = 3; 
            break;
        default:
            return null;
    }
    return [
        'current' => $current,
        'required' => (int)$required,
        'percentage' => $required > 0 ? min(100, round(($current / $required) * 100)) : 0
    ];
}