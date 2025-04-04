<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['slug'])) {
    header("Location: index.php");
    exit;
}

$category_slug = $_GET['slug'];
$category = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug");
$category->bindParam(':slug', $category_slug);
$category->execute();
$category = $category->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("HTTP/1.0 404 Not Found");
    $page_title = "分类未找到";
    require_once 'includes/header.php';
    echo '<section class="error-404"><h1>分类未找到</h1><p>您访问的分类不存在。</p></section>';
    require_once 'includes/footer.php';
    exit;
}

$posts = get_posts_by_category($category_slug);

$page_title = $category['name'] . ' - ' . SITE_TITLE;
$page_description = $category['description'] ?: "查看" . $category['name'] . "分类下的所有文章";

require_once 'includes/header.php';
?>

<section class="category-header">
    <h1><?php echo htmlspecialchars($category['name']); ?></h1>
    <?php if ($category['description']): ?>
        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
    <?php endif; ?>
</section>

<section class="category-posts">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <article class="post">
                <header class="post-header">
                    <h2><a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                    <div class="post-meta">
                        <span class="author">作者: <?php echo htmlspecialchars($post['username']); ?></span>
                        <span class="date">发布于: <?php echo date('Y-m-d', strtotime($post['published_at'])); ?></span>
                    </div>
                </header>
                <div class="post-content">
                    <?php if ($post['cover_image']): ?>
                        <img src="<?php echo $post['cover_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image">
                    <?php endif; ?>
                    <p><?php echo $post['excerpt'] ? htmlspecialchars($post['excerpt']) : substr(strip_tags($post['content']), 0, 200) . '...'; ?></p>
                    <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>" class="read-more">阅读更多</a>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-posts">该分类下暂无文章</p>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';
?>