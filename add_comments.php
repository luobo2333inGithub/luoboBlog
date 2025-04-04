<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$post_id = intval($_POST['post_id']);
$content = trim($_POST['content']);
$user = get_logged_in_user();

if (empty($content)) {
    $_SESSION['comment_error'] = '评论内容不能为空';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, is_approved) VALUES (:post_id, :user_id, :content, TRUE)");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->bindParam(':content', $content);
    $stmt->execute();
    
    $_SESSION['comment_success'] = '评论已提交';
} catch (PDOException $e) {
    $_SESSION['comment_error'] = '评论提交失败: ' . $e->getMessage();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>