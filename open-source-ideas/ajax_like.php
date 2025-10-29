<?php
require_once 'config.php';
require_once 'functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод']);
    exit;
}
$ideaId = (int)($_POST['idea_id'] ?? 0);
if ($ideaId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID идеи']);
    exit;
}
try {
    $liked = toggleLike($ideaId, $_SESSION['user_id']);
    $count = getLikesCount($ideaId);
    checkAndAwardBadges($_SESSION['user_id']);
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'count' => $count
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}