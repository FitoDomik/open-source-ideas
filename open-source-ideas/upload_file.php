<?php
require_once 'config.php';
header('Content-Type: application/json');
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти']);
    exit;
}
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен']);
    exit;
}
$ideaId = (int)($_POST['idea_id'] ?? 0);
$file = $_FILES['file'];
$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/zip',
    'video/mp4',
    'video/mpeg',
    'video/quicktime'
];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Недопустимый тип файла']);
    exit;
}
if ($file['size'] > 100 * 1024 * 1024) { // 100 МБ
    echo json_encode(['success' => false, 'message' => 'Файл слишком большой (максимум 100 МБ)']);
    exit;
}
try {
    $uploadDir = 'upload/files/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'file_' . $ideaId . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO idea_files (idea_id, filename, original_name, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ideaId, $filename, $file['name'], $file['type'], $file['size']]);
        echo json_encode([
            'success' => true,
            'filename' => $filename,
            'original_name' => $file['name'],
            'size' => $file['size']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка сохранения файла']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}