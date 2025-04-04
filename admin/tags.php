<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = '标签名称不能为空';
    }
    
    if (empty($slug)) {
        $slug = generate_slug($name);
    } else {
        $slug = generate_slug($slug);
    }
    
    // 检查slug是否唯一
    $stmt = $pdo->prepare("SELECT id FROM tags WHERE slug = :slug");
    $stmt->bindParam(':slug', $slug);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        $errors[] = 'URL别名已被使用，请换一个';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (:name, :slug)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            
            $_SESSION['success_message'] = '标签添加成功';
            header("Location: tags.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = '数据库错误: ' . $e->getMessage();
        }
    }
}

// 获取所有标签
$tags = $pdo->query("SELECT t.*, COUNT(pt.post_id) as post_count 
                    FROM tags t 
                    LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                    GROUP BY t.id 
                    ORDER BY t.name ASC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "标签管理 - " . SITE_TITLE;
require_once 'includes/admin-header.php';
?>
