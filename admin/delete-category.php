<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit;
}

$id = intval($_GET['id']);

// 检查分类是否有文章
$stmt = $pdo->prepare("SELECT COUNT(*) FROM post_categories WHERE category_id = :category_id");
$stmt->bindParam(':category_id', $id);
$stmt->execute();
$post_count = $stmt->fetchColumn();

if ($post_count > 0) {
    $_SESSION['error_message'] = '无法删除包含文章的分类';
    header("Location: categories.php");
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $_SESSION['success_message'] = '分类删除成功';
} catch (PDOException $e) {
    $_SESSION['error_message'] = '删除分类失败: ' . $e->getMessage();
}

header("Location: categories.php");
exit;
?>