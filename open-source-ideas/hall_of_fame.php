<?php
require_once 'config.php';
$db = getDB();
$stmt = $db->query("
    SELECT 
        u.id,
        u.username,
        COUNT(r.id) as response_count,
        u.created_at
    FROM users u
    LEFT JOIN responses r ON u.id = r.user_id
    GROUP BY u.id
    HAVING response_count > 0
    ORDER BY response_count DESC, u.created_at ASC
    LIMIT 50
");
$topDevelopers = $stmt->fetchAll();
$statsStmt = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM ideas) as total_ideas,
        (SELECT COUNT(*) FROM responses) as total_responses
");
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Зал славы - Топ разработчиков и активных участников сообщества</title>
    <meta name="description" content="Лучшие разработчики платформы идей. Рейтинг участников, которые помогают воплощать идеи в жизнь. Стань частью open source сообщества!">
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <header class="page-header">
            <h1>🏆 Зал славы</h1>
            <p class="subtitle">Лучшие разработчики нашей платформы</p>
        </header>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Пользователей</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_ideas'] ?></div>
                <div class="stat-label">Идей</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_responses'] ?></div>
                <div class="stat-label">Ответов</div>
            </div>
        </div>
        <?php if (empty($topDevelopers)): ?>
            <div class="empty-state">
                <h2>😔 Пока нет данных</h2>
                <p>Зал славы пуст. Станьте первым разработчиком, который ответит на идею!</p>
                <a href="index.php" class="btn btn-primary">Посмотреть идеи</a>
            </div>
        <?php else: ?>
            <div class="leaderboard">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Место</th>
                            <th>Разработчик</th>
                            <th>Ответов</th>
                            <th>На платформе с</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topDevelopers as $index => $developer): ?>
                            <tr class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                                <td class="rank">
                                    <?php if ($index === 0): ?>
                                        🥇
                                    <?php elseif ($index === 1): ?>
                                        🥈
                                    <?php elseif ($index === 2): ?>
                                        🥉
                                    <?php else: ?>
                                        <?= $index + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td class="username">
                                    <strong><?= h($developer['username']) ?></strong>
                                </td>
                                <td class="response-count">
                                    <span class="badge"><?= $developer['response_count'] ?></span>
                                </td>
                                <td class="join-date">
                                    <?= date('d.m.Y', strtotime($developer['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <footer class="footer">
        <div class="container">
            <p>💡 Платформа идей - место, где рождаются проекты</p>
        </div>
    </footer>
</body>
</html>