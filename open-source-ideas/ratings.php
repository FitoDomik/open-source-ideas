<?php
require_once 'config.php';
$db = getDB();
$weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
$topIdeasWeek = $db->prepare("
    SELECT i.*, u.username,
        (SELECT COUNT(*) FROM idea_likes WHERE idea_id = i.id) as likes,
        (SELECT COUNT(*) FROM comments WHERE idea_id = i.id) as comments
    FROM ideas i
    JOIN users u ON i.user_id = u.id
    WHERE i.created_at >= ?
    ORDER BY likes DESC, comments DESC
    LIMIT 10
");
$topIdeasWeek->execute([$weekAgo]);
$topWeek = $topIdeasWeek->fetchAll();
$monthAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
$topIdeasMonth = $db->prepare("
    SELECT i.*, u.username,
        (SELECT COUNT(*) FROM idea_likes WHERE idea_id = i.id) as likes,
        (SELECT COUNT(*) FROM comments WHERE idea_id = i.id) as comments
    FROM ideas i
    JOIN users u ON i.user_id = u.id
    WHERE i.created_at >= ?
    ORDER BY likes DESC, comments DESC
    LIMIT 10
");
$topIdeasMonth->execute([$monthAgo]);
$topMonth = $topIdeasMonth->fetchAll();
$topDevs = $db->query("
    SELECT u.id, u.username,
        (SELECT COUNT(*) FROM responses WHERE user_id = u.id) as responses_count,
        (SELECT COUNT(*) FROM idea_likes il JOIN ideas i ON il.idea_id = i.id WHERE i.user_id = u.id) as likes_received
    FROM users u
    WHERE (SELECT COUNT(*) FROM responses WHERE user_id = u.id) > 0
    ORDER BY responses_count DESC, likes_received DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рейтинги - Платформа идей</title>
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 40px;">🏆 Рейтинги</h1>
        <div class="ratings-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
            <div>
                <h2 style="margin-bottom: 20px;">📅 Топ идей недели</h2>
                <?php $position = 1; foreach ($topWeek as $idea): ?>
                    <div class="rating-card">
                        <div class="rating-position <?= $position <= 3 ? 'top-' . $position : '' ?>">
                            <?= $position ?>
                        </div>
                        <div style="flex: 1;">
                            <h3><a href="idea.php?id=<?= $idea['id'] ?>"><?= h($idea['title']) ?></a></h3>
                            <p style="color: var(--text-secondary); font-size: 14px;">
                                👤 <?= h($idea['username']) ?> • ⭐ <?= $idea['likes'] ?> • 💬 <?= $idea['comments'] ?>
                            </p>
                        </div>
                    </div>
                <?php $position++; endforeach; ?>
                <?php if (empty($topWeek)): ?>
                    <div class="empty-state"><p>Пока нет данных</p></div>
                <?php endif; ?>
            </div>
            <div>
                <h2 style="margin-bottom: 20px;">📊 Топ идей месяца</h2>
                <?php $position = 1; foreach ($topMonth as $idea): ?>
                    <div class="rating-card">
                        <div class="rating-position <?= $position <= 3 ? 'top-' . $position : '' ?>">
                            <?= $position ?>
                        </div>
                        <div style="flex: 1;">
                            <h3><a href="idea.php?id=<?= $idea['id'] ?>"><?= h($idea['title']) ?></a></h3>
                            <p style="color: var(--text-secondary); font-size: 14px;">
                                👤 <?= h($idea['username']) ?> • ⭐ <?= $idea['likes'] ?> • 💬 <?= $idea['comments'] ?>
                            </p>
                        </div>
                    </div>
                <?php $position++; endforeach; ?>
                <?php if (empty($topMonth)): ?>
                    <div class="empty-state"><p>Пока нет данных</p></div>
                <?php endif; ?>
            </div>
        </div>
        <div style="margin-top: 40px;">
            <h2 style="margin-bottom: 20px;">💻 Топ разработчиков</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 15px;">
                <?php $position = 1; foreach ($topDevs as $dev): ?>
                    <div class="rating-card">
                        <div class="rating-position <?= $position <= 3 ? 'top-' . $position : '' ?>">
                            <?= $position ?>
                        </div>
                        <div style="flex: 1;">
                            <h3><a href="profile.php?id=<?= $dev['id'] ?>"><?= h($dev['username']) ?></a></h3>
                            <p style="color: var(--text-secondary); font-size: 14px;">
                                💻 Ответов: <?= $dev['responses_count'] ?> • ⭐ Лайков: <?= $dev['likes_received'] ?>
                            </p>
                        </div>
                    </div>
                <?php $position++; endforeach; ?>
            </div>
            <?php if (empty($topDevs)): ?>
                <div class="empty-state"><p>Пока нет данных</p></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>