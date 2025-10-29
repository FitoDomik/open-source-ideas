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
$status = $_POST['status'] ?? '';
try {
    if (updateIdeaStatus($ideaId, $status, $_SESSION['user_id'])) {
        checkAndAwardBadges($_SESSION['user_id']);
        echo json_encode([
            'success' => true,
            'status' => $status,
            'status_name' => getStatusName($status),
            'status_color' => getStatusColor($status)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка обновления статуса']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}