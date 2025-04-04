<?php 
if (!isset($page_title)) {
    $page_title = '管理后台 - ' . SITE_TITLE;
}
 
// 确保会话已启动 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
 
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
</head>
<body>
    <div class="admin-notifications">
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="notification success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="notification error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>
    </div>