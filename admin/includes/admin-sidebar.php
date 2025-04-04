<?php 
/**
* 博客管理系统 - 后台侧边栏 
* 存放路径：/myblog/includes/admin-sidebar.php  
*/
 
// 安全验证（防止直接访问）
defined('IN_ADMIN') or die('Access Denied');
 
// 确保会话已启动 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
 
// 获取当前用户信息 
$user = isset($_SESSION['user_id']) ? get_logged_in_user() : null;
 
// 获取当前页面以高亮菜单项 
$current_page = basename($_SERVER['PHP_SELF']);
?>
 
<div class="admin-sidebar">
    <div class="admin-profile">
        <?php if ($user && isset($user['avatar'])): ?>
        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>" class="profile-avatar">
        <?php endif; ?>
        <?php if ($user): ?>
        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
        <p><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
        <?php endif; ?>
    </div>
    <nav class="admin-menu">
        <ul>
            <li class="<?php echo $current_page === 'index.php'  ? 'active' : ''; ?>"><a href="index.php"><i  class="fas fa-tachometer-alt"></i> 仪表盘</a></li>
            <li class="<?php echo in_array($current_page, ['posts.php',  'edit-post.php'])  ? 'active' : ''; ?>"><a href="posts.php"><i  class="fas fa-newspaper"></i> 文章管理</a></li>
            <li class="<?php echo in_array($current_page, ['categories.php',  'edit-category.php'])  ? 'active' : ''; ?>"><a href="categories.php"><i  class="fas fa-folder"></i> 分类管理</a></li>
            <li class="<?php echo $current_page === 'tags.php'  ? 'active' : ''; ?>"><a href="tags.php"><i  class="fas fa-tags"></i> 标签管理</a></li>
            <li class="<?php echo $current_page === 'comments.php'  ? 'active' : ''; ?>"><a href="comments.php"><i  class="fas fa-comments"></i> 评论管理</a></li>
            <li class="<?php echo $current_page === 'users.php'  ? 'active' : ''; ?>"><a href="users.php"><i  class="fas fa-users"></i> 用户管理</a></li>
            <li><a href="../logout.php"><i  class="fas fa-sign-out-alt"></i> 退出登录</a></li>
        </ul>
    </nav>
</div>