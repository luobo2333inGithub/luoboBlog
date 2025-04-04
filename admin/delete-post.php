<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    header("Location: posts.php");
    exit;
}

try {
    $pdo->beginTransaction();
    
    // 删除关联的分类和标签
    $pdo->exec("DELETE FROM post_categories WHERE post_id = $id");
    $pdo->exec("DELETE FROM post_tags WHERE post_id = $id");
    
    // 删除文章
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $pdo->commit();
    
    $_SESSION['success_message'] = '文章删除成功';
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = '删除文章失败: ' . $e->getMessage();
}

header("Location: posts.php");
exit;
?>