<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' . SITE_TITLE : SITE_TITLE; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : SITE_DESCRIPTION; ?>">
    
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>"><?php echo SITE_TITLE; ?></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>">首页</a></li>
                    <?php foreach (get_all_categories() as $category): ?>
                    <li><a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>"><?php echo $category['name']; ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?php echo SITE_URL; ?>/search.php">搜索</a></li>
                    <?php if (is_logged_in()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/admin/">管理</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/logout.php">登出</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/login.php">登录</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <button class="menu-toggle" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    
    <main class="container">