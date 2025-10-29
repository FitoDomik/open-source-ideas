<?php
require_once 'config.php';
header('Content-Type: application/json');
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти']);
    exit;
}
$followingId = (int)($_POST['user_id'] ?? 0);
$followerId = $_SESSION['user_id'];
if ($followerId == $followingId) {
    echo json_encode(['success' => false, 'message' => 'Нельзя подписаться на себя']);
    exit;
}
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM subscriptions WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$followerId, $followingId]);
    $exists = $stmt->fetch();
    if ($exists) {
        $stmt = $db->prepare("DELETE FROM subscriptions WHERE follower_id = ? AND following_id = ?");
        $stmt->execute([$followerId, $followingId]);
        $subscribed = false;
    } else {
        $stmt = $db->prepare("INSERT INTO subscriptions (follower_id, following_id) VALUES (?, ?)");
        $stmt->execute([$followerId, $followingId]);
        $subscribed = true;
    }
    $stmt = $db->prepare("SELECT COUNT(*) FROM subscriptions WHERE following_id = ?");
    $stmt->execute([$followingId]);
    $count = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'subscribed' => $subscribed, 'count' => $count]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}