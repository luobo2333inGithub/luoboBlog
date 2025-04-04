<?php 
require_once 'includes/config.php'; 
require_once 'includes/functions.php'; 
 
if (is_logged_in()) {
    header("Location: admin/");
    exit;
}
 
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
 
    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } elseif (login_user($username, $password)) {
        // 登录成功后设置会话变量 
        $user = get_logged_in_user();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = ($user['username'] === 'admin'); // 设置管理员标志 
        
        // 重定向到管理后台 
        header("Location: " . SITE_URL . "/admin/");
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
 
$page_title = "登录 - " . SITE_TITLE;
require_once 'includes/header.php'; 
?>
 
<section class="login-section">
    <h1>登录</h1>
 
    <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
 
    <form method="POST" action="login.php"  class="login-form">
        <div class="form-group">
            <label for="username">用户名</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">密码</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="login-btn">登录</button>
        </div>
    </form>
</section>
 
<?php 
require_once 'includes/footer.php'; 
?>