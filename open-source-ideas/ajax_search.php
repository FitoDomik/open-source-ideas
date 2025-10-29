<?php
require_once 'config.php';
header('Content-Type: application/json');
$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}
$db = getDB();
$stmt = $db->prepare("SELECT id, title FROM ideas WHERE title LIKE ? OR tags LIKE ? LIMIT 5");
$searchTerm = "%$query%";
$stmt->execute([$searchTerm, $searchTerm]);
$results = $stmt->fetchAll();
echo json_encode($results);
