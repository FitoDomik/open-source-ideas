<?php
require_once 'config.php';
header('Content-Type: application/xml; charset=utf-8');
$baseUrl = SITE_URL;
$db = getDB();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
$staticPages = [
    ['url' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['url' => '/hall_of_fame.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['url' => '/register.php', 'changefreq' => 'monthly', 'priority' => '0.6'],
    ['url' => '/login.php', 'changefreq' => 'monthly', 'priority' => '0.5'],
    ['url' => '/add_idea.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
];
foreach ($staticPages as $page) {
    echo "    <url>\n";
    echo "        <loc>" . htmlspecialchars($baseUrl . $page['url']) . "</loc>\n";
    echo "        <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "        <priority>{$page['priority']}</priority>\n";
    echo "    </url>\n";
}
$stmt = $db->query("SELECT id, created_at FROM ideas ORDER BY created_at DESC");
while ($idea = $stmt->fetch()) {
    $lastmod = date('Y-m-d', strtotime($idea['created_at']));
    echo "    <url>\n";
    echo "        <loc>" . htmlspecialchars($baseUrl . '/idea.php?id=' . $idea['id']) . "</loc>\n";
    echo "        <lastmod>{$lastmod}</lastmod>\n";
    echo "        <changefreq>weekly</changefreq>\n";
    echo "        <priority>0.9</priority>\n";
    echo "    </url>\n";
}
echo '</urlset>';
if (isset($_GET['save'])) {
    ob_start();
    include($_SERVER['PHP_SELF']);
    $content = ob_get_clean();
    file_put_contents(__DIR__ . '/sitemap.xml', $content);
    echo "Sitemap успешно обновлен!";
}