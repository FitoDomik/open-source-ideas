<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'u3307679_open-source-ideas'); 
define('DB_PASS', 'u3307679_open-source-ideas'); 
define('DB_NAME', 'u3307679_open-source-ideas');
define('SITE_URL', 'https://farm429.ru');
define('UPLOAD_DIR', __DIR__ . '/upload/');
define('MAX_FILE_SIZE', 25 * 1024 * 1024);
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
    return $pdo;
}
ini_set('session.gc_maxlifetime', 31536000); 
session_set_cookie_params([
    'lifetime' => 31536000, 
    'path' => '/',
    'domain' => '', 
    'secure' => isset($_SERVER['HTTPS']), 
    'httponly' => true, 
    'samesite' => 'Lax' 
]);
session_start();
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
function uploadImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception("Файл слишком большой. Максимум 25 МБ.");
    }
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, $allowed)) {
        throw new Exception("Недопустимый формат файла. Разрешены: JPG, PNG, GIF, WEBP.");
    }
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception("Ошибка при загрузке файла.");
    }
    return $filename;
}