<?php
require_once 'config.php';
require_once 'functions.php';
$searchQuery = trim($_GET['q'] ?? '');
$sortBy = $_GET['sort'] ?? 'date_desc';
$filterStatus = $_GET['status'] ?? '';
$filterAuthor = $_GET['author'] ?? '';
$filterTag = $_GET['tag'] ?? '';
$db = getDB();
$where = ['1=1'];
$params = [];
if (!empty($searchQuery)) {
    $where[] = "(i.title LIKE ? OR i.description LIKE ? OR i.tags LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}
if (!empty($filterStatus)) {
    $where[] = "i.status = ?";
    $params[] = $filterStatus;
}
if (!empty($filterAuthor)) {
    $where[] = "i.user_id = ?";
    $params[] = (int)$filterAuthor;
}
if (!empty($filterTag)) {
    $where[] = "i.tags LIKE ?";
    $params[] = "%$filterTag%";
}
$orderBy = "i.created_at DESC";
switch ($sortBy) {
    case 'date_asc':
        $orderBy = "i.created_at ASC";
        break;
    case 'likes':
        $orderBy = "likes_count DESC";
        break;
    case 'comments':
        $orderBy = "comments_count DESC";
        break;
    case 'responses':
        $orderBy = "responses_count DESC";
        break;
}
$sql = "
    SELECT i.*, u.username,
        (SELECT COUNT(*) FROM idea_likes WHERE idea_id = i.id) as likes_count,
        (SELECT COUNT(*) FROM comments WHERE idea_id = i.id) as comments_count,
        (SELECT COUNT(*) FROM responses WHERE idea_id = i.id) as responses_count
    FROM ideas i
    JOIN users u ON i.user_id = u.id
    WHERE " . implode(" AND ", $where) . "
    ORDER BY $orderBy
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$ideas = $stmt->fetchAll();
$allUsers = $db->query("SELECT id, username FROM users ORDER BY username")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск идей - Платформа идей</title>
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="search-container">
            <form method="GET" action="">
                <div class="search-box">
                    <input type="text" 
                           name="q" 
                           class="search-input" 
                           placeholder="Поиск идей..." 
                           value="<?= h($searchQuery) ?>"
                           autocomplete="off"
                           id="search-input">
                    <span class="search-icon">🔍</span>
                    <div class="autocomplete-results" id="autocomplete-results"></div>
                </div>
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="sort">Сортировка</label>
                        <select name="sort" id="sort" class="filter-select">
                            <option value="date_desc" <?= $sortBy == 'date_desc' ? 'selected' : '' ?>>Сначала новые</option>
                            <option value="date_asc" <?= $sortBy == 'date_asc' ? 'selected' : '' ?>>Сначала старые</option>
                            <option value="likes" <?= $sortBy == 'likes' ? 'selected' : '' ?>>По лайкам</option>
                            <option value="comments" <?= $sortBy == 'comments' ? 'selected' : '' ?>>По комментариям</option>
                            <option value="responses" <?= $sortBy == 'responses' ? 'selected' : '' ?>>По ответам</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status">Статус</label>
                        <select name="status" id="status" class="filter-select">
                            <option value="">Все статусы</option>
                            <option value="new" <?= $filterStatus == 'new' ? 'selected' : '' ?>>🆕 Новые</option>
                            <option value="in_progress" <?= $filterStatus == 'in_progress' ? 'selected' : '' ?>>🔨 В разработке</option>
                            <option value="completed" <?= $filterStatus == 'completed' ? 'selected' : '' ?>>✅ Завершенные</option>
                            <option value="abandoned" <?= $filterStatus == 'abandoned' ? 'selected' : '' ?>>⏸ Заброшенные</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="author">Автор</label>
                        <select name="author" id="author" class="filter-select">
                            <option value="">Все авторы</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $filterAuthor == $user['id'] ? 'selected' : '' ?>>
                                    <?= h($user['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="tag">Тег</label>
                        <input type="text" name="tag" id="tag" class="filter-select" placeholder="Введите тег..." value="<?= h($filterTag) ?>">
                    </div>
                </div>
                <div style="margin-top: 15px; text-align: right;">
                    <button type="submit" class="btn btn-primary">🔍 Поиск</button>
                    <a href="search.php" class="btn">Сбросить</a>
                </div>
            </form>
        </div>
        <?php if (!empty($searchQuery) || !empty($filterStatus) || !empty($filterAuthor) || !empty($filterTag)): ?>
            <div style="margin-bottom: 20px;">
                <h2>Результаты поиска: <?= count($ideas) ?></h2>
            </div>
        <?php endif; ?>
        <?php if (empty($ideas)): ?>
            <div class="empty-state">
                <p>😕 Ничего не найдено. Попробуйте изменить параметры поиска.</p>
            </div>
        <?php else: ?>
            <div class="ideas-grid">
                <?php foreach ($ideas as $idea): ?>
                    <div class="idea-card">
                        <?php if ($idea['image']): ?>
                            <div class="idea-image">
                                <img src="upload/<?= h($idea['image']) ?>" alt="<?= h($idea['title']) ?>" loading="lazy">
                            </div>
                        <?php endif; ?>
                        <div class="idea-content">
                            <h3><?= h($idea['title']) ?></h3>
                            <p class="idea-description"><?= h(mb_substr($idea['description'], 0, 150)) ?>...</p>
                            <?php if ($idea['tags']): ?>
                                <div class="idea-tags">
                                    <?php foreach (array_slice(explode(',', $idea['tags']), 0, 3) as $tag): ?>
                                        <span class="tag"><?= h(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div style="margin: 15px 0; display: flex; gap: 15px; font-size: 14px;">
                                <span>⭐ <?= $idea['likes_count'] ?></span>
                                <span>💬 <?= $idea['comments_count'] ?></span>
                                <span>💻 <?= $idea['responses_count'] ?></span>
                            </div>
                            <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-primary btn-block">Подробнее</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
        const searchInput = document.getElementById('search-input');
        const autocompleteResults = document.getElementById('autocomplete-results');
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            if (query.length < 2) {
                autocompleteResults.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch('ajax_search.php?q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => {
                        if (data.length > 0) {
                            autocompleteResults.innerHTML = data.map(item => 
                                `<div class="autocomplete-item" onclick="window.location.href='idea.php?id=${item.id}'">${item.title}</div>`
                            ).join('');
                            autocompleteResults.style.display = 'block';
                        } else {
                            autocompleteResults.style.display = 'none';
                        }
                    });
            }, 300);
        });
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target)) {
                autocompleteResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>