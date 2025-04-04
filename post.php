<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['slug']) {
    header("Location: index.php");
    exit;
}

$post = get_post($_GET['slug']);

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    $page_title = "文章未找到";
    require_once 'includes/header.php';
    echo '<section class="error-404"><h1>文章未找到</h1><p>您访问的文章不存在或已被删除。</p></section>';
    require_once 'includes/footer.php';
    exit;
}

$page_title = $post['title'];
$page_description = $post['excerpt'] ?: substr(strip_tags($post['content']), 0, 150);
$categories = get_post_categories($post['id']);
$tags = get_post_tags($post['id']);

require_once 'includes/header.php';
?>

<article class="single-post">
    <header class="post-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">
            <div class="author-info">
                <?php if ($post['avatar']): ?>
                    <img src="<?php echo $post['avatar']; ?>" alt="<?php echo htmlspecialchars($post['username']); ?>" class="author-avatar">
                <?php endif; ?>
                <span class="author-name"><?php echo htmlspecialchars($post['username']); ?></span>
            </div>
            <span class="post-date"><?php echo date('Y年m月d日', strtotime($post['published_at'])); ?></span>
            <?php if (!empty($categories)): ?>
                <span class="post-categories">
                    <?php foreach ($categories as $category): ?>
                        <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>" class="category-badge"><?php echo htmlspecialchars($category['name']); ?></a>
                    <?php endforeach; ?>
                </span>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($post['cover_image']): ?>
        <div class="post-cover">
            <img src="<?php echo $post['cover_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
        </div>
    <?php endif; ?>

    <div class="post-content">
        <?php echo markdown_to_html($post['content']); ?>
    </div>

    <?php if (!empty($tags)): ?>
        <footer class="post-footer">
            <div class="post-tags">
                <span>标签:</span>
                <?php foreach ($tags as $tag): ?>
                    <a href="#" class="tag-link"><?php echo htmlspecialchars($tag['name']); ?></a>
                <?php endforeach; ?>
            </div>
        </footer>
    <?php endif; ?>
</article>

<section class="comments-section">
    <h2>评论</h2>
    
    <?php if (is_logged_in()): ?>
        <form method="POST" action="<?php echo SITE_URL; ?>/add_comment.php" class="comment-form">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <div class="form-group">
                <textarea name="content" placeholder="写下你的评论..." required></textarea>
            </div>
            <button type="submit" class="submit-btn">提交评论</button>
        </form>
    <?php else: ?>
        <p class="login-to-comment">请<a href="<?php echo SITE_URL; ?>/login.php">登录</a>后发表评论</p>
    <?php endif; ?>
    
    <div class="comments-list">
        <?php
        $stmt = $pdo->prepare("SELECT c.*, u.username, u.avatar 
                              FROM comments c 
                              LEFT JOIN users u ON c.user_id = u.id 
                              WHERE c.post_id = :post_id AND c.is_approved = TRUE 
                              ORDER BY c.created_at DESC");
        $stmt->bindParam(':post_id', $post['id']);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($comments)): 
            foreach ($comments as $comment): 
        ?>
            <div class="comment">
                <div class="comment-author">
                    <?php if ($comment['avatar']): ?>
                        <img src="<?php echo $comment['avatar']; ?>" alt="<?php echo htmlspecialchars($comment['username'] ?: $comment['author_name']); ?>" class="comment-avatar">
                    <?php endif; ?>
                    <span class="author-name"><?php echo htmlspecialchars($comment['username'] ?: $comment['author_name']); ?></span>
                </div>
                <div class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></div>
                <div class="comment-content"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>
            </div>
        <?php 
            endforeach; 
        else: 
        ?>
            <p class="no-comments">暂无评论</p>
        <?php endif; ?>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>