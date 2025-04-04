<?php

// 必须放在文件最开头，任何输出之前 
define('IN_ADMIN', true);
session_start();
 
// 用户登录验证（示例）
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php'); 
    exit;
}
 
require_once __DIR__.'/../includes/admin-sidebar.php'; 
require_once '../includes/config.php';
require_once '../includes/functions.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 搜索和过滤处理
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$query = "SELECT p.*, u.username 
          FROM posts p 
          JOIN users u ON p.author_id = u.id 
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (p.title LIKE :search OR p.content LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status === 'published') {
    $query .= " AND p.is_published = TRUE";
} elseif ($status === 'draft') {
    $query .= " AND p.is_published = FALSE";
}

$query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

// 获取文章数据
$stmt = $pdo->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取总数用于分页
$count_query = "SELECT COUNT(*) FROM posts p WHERE 1=1";
if (!empty($search)) {
    $count_query .= " AND (p.title LIKE '%$search%' OR p.content LIKE '%$search%')";
}
if ($status === 'published') {
    $count_query .= " AND p.is_published = TRUE";
} elseif ($status === 'draft') {
    $count_query .= " AND p.is_published = FALSE";
}

$total_posts = $pdo->query($count_query)->fetchColumn();
$total_pages = ceil($total_posts / $limit);

$page_title = "文章管理 - " . SITE_TITLE;
require_once 'includes/admin-header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/admin-sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <h1>文章管理</h1>
            <a href="edit-post.php" class="btn btn-primary">新增文章</a>
        </div>
        
        <div class="filters">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="搜索文章..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <div class="status-filter">
                <a href="?status=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="<?php echo $status === 'all' ? 'active' : ''; ?>">全部</a>
                <a href="?status=published<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="<?php echo $status === 'published' ? 'active' : ''; ?>">已发布</a>
                <a href="?status=draft<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="<?php echo $status === 'draft' ? 'active' : ''; ?>">草稿</a>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>标题</th>
                    <th>作者</th>
                    <th>状态</th>
                    <th>发布日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <a href="edit-post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                                <?php if ($post['is_published']): ?>
                                    <a href="../post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="view-link"><i class="fas fa-external-link-alt"></i></a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($post['username']); ?></td>
                            <td>
                                <?php if ($post['is_published']): ?>
                                    <span class="badge success">已发布</span>
                                <?php else: ?>
                                    <span class="badge warning">草稿</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $post['published_at'] ? date('Y-m-d', strtotime($post['published_at'])) : '-'; ?>
                            </td>
                            <td class="actions">
                                <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-edit"><i class="fas fa-edit"></i> 编辑</a>
                                <form action="delete-post.php" method="POST" class="inline-form" onsubmit="return confirm('确定要删除这篇文章吗？');">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-delete"><i class="fas fa-trash"></i> 删除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">没有找到文章</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status !== 'all' ? '&status=' . $status : ''; ?>" class="page-link">&laquo; 上一页</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status !== 'all' ? '&status=' . $status : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status !== 'all' ? '&status=' . $status : ''; ?>" class="page-link">下一页 &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php
require_once 'includes/admin-footer.php';
?>