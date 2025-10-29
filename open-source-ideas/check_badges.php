<?php
require_once 'config.php';
require_once 'functions.php';
$db = getDB();
$users = $db->query("SELECT id, username FROM users")->fetchAll();
$totalChecked = 0;
$totalAwarded = 0;
$awarded = [];
foreach ($users as $user) {
    $before = count(getUserBadges($user['id']));
    checkAndAwardBadges($user['id']);
    $after = count(getUserBadges($user['id']));
    $newBadges = $after - $before;
    $totalChecked++;
    if ($newBadges > 0) {
        $totalAwarded += $newBadges;
        $awarded[] = [
            'user' => $user['username'],
            'badges' => $newBadges
        ];
    }
}
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка достижений</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .result {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success {
            color: #22c55e;
            font-weight: bold;
        }
        .info {
            color: #6366f1;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 8px;
            margin: 5px 0;
            background: #f9fafb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="result">
        <h1>🏆 Проверка достижений завершена</h1>
        <p class="info">Проверено пользователей: <strong><?= $totalChecked ?></strong></p>
        <?php if ($totalAwarded > 0): ?>
            <p class="success">Выдано достижений: <strong><?= $totalAwarded ?></strong></p>
            <h3>Пользователи, получившие достижения:</h3>
            <ul>
                <?php foreach ($awarded as $item): ?>
                    <li><?= h($item['user']) ?> — получено <?= $item['badges'] ?> достижений</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="info">Новых достижений не обнаружено. Все пользователи уже имеют соответствующие награды.</p>
        <?php endif; ?>
        <p style="margin-top: 30px;">
            <a href="profile.php" style="color: #6366f1;">← Вернуться в профиль</a> |
            <a href="index.php" style="color: #6366f1;">На главную</a>
        </p>
    </div>
</body>
</html>