<?php
require_once 'config.php';
$tagFilter = $_GET['tag'] ?? '';
$db = getDB();
if ($tagFilter) {
    $stmt = $db->prepare("
        SELECT i.*, u.username 
        FROM ideas i 
        JOIN users u ON i.user_id = u.id 
        WHERE i.tags LIKE ?
        ORDER BY i.created_at DESC
    ");
    $stmt->execute(['%' . $tagFilter . '%']);
} else {
    $stmt = $db->query("
        SELECT i.*, u.username 
        FROM ideas i 
        JOIN users u ON i.user_id = u.id 
        ORDER BY i.created_at DESC
    ");
}
$ideas = $stmt->fetchAll();
$tagsStmt = $db->query("SELECT DISTINCT tags FROM ideas WHERE tags IS NOT NULL AND tags != ''");
$allTags = [];
foreach ($tagsStmt->fetchAll() as $row) {
    if (!empty($row['tags'])) {
        $tags = array_map('trim', explode(',', $row['tags']));
        $allTags = array_merge($allTags, $tags);
    }
}
$allTags = array_unique(array_filter($allTags));
sort($allTags);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Платформа идей - Open Source проекты, стартап идеи и совместная разработка</title>
    <meta name="description" content="Платформа для обмена идеями между разработчиками. Найди открытые проекты, стартап идеи для реализации. Сообщество для совместной работы над инновационными проектами.">
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <header class="page-header">
            <h1>🚀 Идеи для разработчиков</h1>
            <p class="subtitle">Найди интересный проект и воплоти его в жизнь. Открытые проекты, стартап идеи, совместная разработка.</p>
        </header>
        <?php if (!empty($allTags)): ?>
        <div class="tags-filter">
            <strong>Фильтр по тегам:</strong>
            <a href="index.php" class="tag <?= empty($tagFilter) ? 'active' : '' ?>">Все</a>
            <?php foreach ($allTags as $tag): ?>
                <a href="?tag=<?= urlencode($tag) ?>" 
                   class="tag <?= $tagFilter === $tag ? 'active' : '' ?>">
                    <?= h($tag) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (empty($ideas)): ?>
            <div class="empty-state">
                <h2>😔 Пока нет идей</h2>
                <p>Будьте первым, кто добавит интересную идею!</p>
                <?php if (isLoggedIn()): ?>
                    <a href="add_idea.php" class="btn btn-primary">Добавить идею</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">Зарегистрироваться</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="ideas-grid">
                <?php foreach ($ideas as $idea): ?>
                    <div class="idea-card">
                        <?php if ($idea['image']): ?>
                            <div class="idea-image">
                                <img data-src="upload/<?= h($idea['image']) ?>" 
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23f0f0f0' width='400' height='300'/%3E%3C/svg%3E"
                                     alt="<?= h($idea['title']) ?>" 
                                     loading="lazy"
                                     class="lazy-placeholder">
                            </div>
                        <?php endif; ?>
                        <div class="idea-content">
                            <h3><?= h($idea['title']) ?></h3>
                            <p class="idea-description">
                                <?= h(mb_substr($idea['description'], 0, 150)) ?>
                                <?= mb_strlen($idea['description']) > 150 ? '...' : '' ?>
                            </p>
                            <?php if ($idea['tags']): ?>
                                <div class="idea-tags">
                                    <?php foreach (explode(',', $idea['tags']) as $tag): ?>
                                        <span class="tag"><?= h(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="idea-footer">
                                <span class="idea-author">👤 <?= h($idea['username']) ?></span>
                                <span class="idea-date">📅 <?= date('d.m.Y', strtotime($idea['created_at'])) ?></span>
                            </div>
                            <?php 
                            if (!function_exists('getLikesCount')) {
                                require_once 'functions.php';
                            }
                            $likesCount = getLikesCount($idea['id']);
                            $status = $idea['status'] ?? 'new';
                            ?>
                            <div class="idea-stats" style="margin: 10px 0; padding: 8px 0; font-size: 14px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <span class="stat-badge">⭐ <?= $likesCount ?> лайков</span>
                                <?php if ($status): ?>
                                    <span class="status-badge status-<?= $status ?>">
                                        <?= getStatusName($status) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (isLoggedIn()): 
                                    $isFavorite = false;
                                    $favCheck = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND idea_id = ?");
                                    $favCheck->execute([$_SESSION['user_id'], $idea['id']]);
                                    $isFavorite = $favCheck->fetch() !== false;
                                ?>
                                    <button class="favorite-btn <?= $isFavorite ? 'favorited' : '' ?>" 
                                            onclick="toggleFavorite(<?= $idea['id'] ?>, this)"
                                            title="<?= $isFavorite ? 'Удалить из избранного' : 'Добавить в избранное' ?>">
                                        <?= $isFavorite ? '⭐' : '☆' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-primary btn-block">
                                Подробнее
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
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