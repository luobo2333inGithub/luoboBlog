<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_USER', 'ser998889945656');
define('DB_PASS', 'P5kKIdqX3kWS');
define('DB_NAME', 'ser998889945656');

// 网站基本配置
define('SITE_TITLE', '我的个人博客');
define('SITE_DESCRIPTION', '分享我的技术经验和生活感悟');
define('SITE_URL', 'http://blog.luobo2333.131.996h.cn');

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 开启会话
session_start();

// 时区设置
date_default_timezone_set('Asia/Shanghai');
?>