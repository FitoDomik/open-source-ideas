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
$action = $_POST['action'] ?? '';
$ideaId = (int)($_POST['idea_id'] ?? 0);
$text = trim($_POST['text'] ?? '');
if ($action === 'add') {
    if ($ideaId <= 0 || empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Заполните текст комментария']);
        exit;
    }
    try {
        addComment($ideaId, $_SESSION['user_id'], $text);
        checkAndAwardBadges($_SESSION['user_id']);
        $comments = getComments($ideaId);
        echo json_encode([
            'success' => true,
            'comments_count' => count($comments),
            'message' => 'Комментарий добавлен'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif ($action === 'delete') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    try {
        deleteComment($commentId, $_SESSION['user_id']);
        echo json_encode(['success' => true, 'message' => 'Комментарий удалён']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверное действие']);
}