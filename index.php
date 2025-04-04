<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$posts = get_published_posts($limit, $offset);
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts WHERE is_published = TRUE")->fetchColumn();
$total_pages = ceil($total_posts / $limit);

$page_title = SITE_TITLE;
$page_description = SITE_DESCRIPTION;

require_once 'includes/header.php';
?>

<section class="featured-posts">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <article class="post">
                <header class="post-header">
                    <h2><a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                    <div class="post-meta">
                        <span class="author">作者: <?php echo htmlspecialchars($post['username']); ?></span>
                        <span class="date">发布于: <?php echo date('Y-m-d', strtotime($post['published_at'])); ?></span>
                        <?php $categories = get_post_categories($post['id']); ?>
                        <?php if (!empty($categories)): ?>
                            <span class="categories">
                                分类: 
                                <?php foreach ($categories as $index => $category): ?>
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a><?php if ($index < count($categories) - 1) echo ', '; ?>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
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
        
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="prev">上一页</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="next">下一页</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="no-posts">暂无文章发布</p>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';
?>