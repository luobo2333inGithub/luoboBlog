<?php 
define('IN_ADMIN', true); // 用于侧边栏安全验证 
require_once __DIR__.'/../includes/config.php'; 
require_once __DIR__.'/../includes/functions.php'; 
 
// 严格访问控制 
if (!is_logged_in() || !is_admin()) {
    header("Location: " . SITE_URL . "/login.php"); 
    exit;
}
 
// 确保会话已启动 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
 
// 获取当前用户信息并确保它是一个数组 
$user = get_logged_in_user();
if (!is_array($user)) {
    // 如果获取用户信息失败，强制登出 
    header("Location: " . SITE_URL . "/logout.php"); 
    exit;
}
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_published = $pdo->query("SELECT COUNT(*) FROM posts WHERE is_published = TRUE")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
 
$page_title = "管理后台 - " . SITE_TITLE;
require_once 'includes/admin-header.php'; 
?>
 
<div class="admin-container">
    <?php require_once 'includes/admin-sidebar.php';  ?>
 
    <main class="admin-content">
        <h1>仪表盘</h1>
 
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
                <div class="stat-info">
                    <h3>总文章数</h3>
                    <p><?php echo $total_posts; ?></p>
                </div>
            </div>
 
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3>已发布</h3>
                    <p><?php echo $total_published; ?></p>
                </div>
            </div>
 
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-comments"></i></div>
                <div class="stat-info">
                    <h3>总评论数</h3>
                    <p><?php echo $total_comments; ?></p>
                </div>
            </div>
        </div>
 
        <section class="recent-posts">
            <h2>最近文章</h2>
            <?php 
            $stmt = $pdo->query("SELECT p.*, u.username  
                FROM posts p 
                JOIN users u ON p.author_id  = u.id  
                ORDER BY p.created_at  DESC 
                LIMIT 5");
            $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th>作者</th>
                        <th>状态</th>
                        <th>日期</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_posts as $post): ?>
                    <tr>
                        <td><a href="edit-post.php?id=<?php  echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></td>
                        <td><?php echo htmlspecialchars($post['username']); ?></td>
                        <td><?php echo $post['is_published'] ? '<span class="badge success">已发布</span>' : '<span class="badge warning">草稿</span>'; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
 
<?php 
require_once 'includes/admin-footer.php'; 
?>