<?php
require_once 'config.php';
require_once 'functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти']);
    exit;
}
$ideaId = (int)($_POST['idea_id'] ?? 0);
$userId = $_SESSION['user_id'];
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND idea_id = ?");
    $stmt->execute([$userId, $ideaId]);
    $exists = $stmt->fetch();
    if ($exists) {
        $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND idea_id = ?");
        $stmt->execute([$userId, $ideaId]);
        $favorited = false;
    } else {
        $stmt = $db->prepare("INSERT INTO favorites (user_id, idea_id) VALUES (?, ?)");
        $stmt->execute([$userId, $ideaId]);
        $favorited = true;
    }
    $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE idea_id = ?");
    $stmt->execute([$ideaId]);
    $count = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'favorited' => $favorited, 'count' => $count]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}