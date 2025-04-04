<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page_title = "搜索: " . htmlspecialchars($query) . " - " . SITE_TITLE;
$page_description = "搜索 '" . htmlspecialchars($query) . "' 的结果";

require_once 'includes/header.php';
?>

<section class="search-section">
    <h1>搜索</h1>
    <form method="GET" action="search.php" class="search-form">
        <div class="search-box">
            <input type="text" name="q" placeholder="输入关键词..." value="<?php echo htmlspecialchars($query); ?>" required>
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
    
    <?php if (!empty($query)): ?>
        <?php
        $results = search_posts($query);
        $result_count = count($results);
        ?>
        
        <div class="search-results-header">
            <h2>找到 <?php echo $result_count; ?> 条关于 "<?php echo htmlspecialchars($query); ?>" 的结果</h2>
        </div>
        
        <?php if ($result_count > 0): ?>
            <div class="search-results">
                <?php foreach ($results as $post): ?>
                    <article class="search-result">
                        <h3><a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>"><?php echo highlight_keywords($post['title'], $query); ?></a></h3>
                        <div class="result-meta">
                            <span class="author">作者: <?php echo htmlspecialchars($post['username']); ?></span>
                            <span class="date">发布于: <?php echo date('Y-m-d', strtotime($post['published_at'])); ?></span>
                        </div>
                        <div class="result-excerpt">
                            <?php echo highlight_keywords(substr(strip_tags($post['content']), 0, 200) . '...', $query); ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>" class="read-more">阅读全文</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>没有找到匹配的结果。尝试其他关键词？</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';

function highlight_keywords($text, $query) {
    $keywords = preg_split('/\s+/', $query);
    foreach ($keywords as $keyword) {
        if (strlen($keyword) > 2) { // 只高亮长度大于2的关键词
            $text = preg_replace("/(" . preg_quote($keyword, '/') . ")/i", "<span class=\"highlight\">$1</span>", $text);
        }
    }
    return $text;
}
?>