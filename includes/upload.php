<?php 
require_once 'config.php'; 
require_once 'functions.php'; 
 
if (!is_logged_in()) {
    http_response_code(403);
    exit;
}
 
$upload_dir = '../uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
 
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
 
if (isset($_FILES['upload'])) {
    $file = $_FILES['upload'];
    
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['error' => '只允许上传JPEG、PNG和GIF图片']);
        exit;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode([
            'url' => SITE_URL . '/uploads/' . $filename 
        ]);
    } else {
        echo json_encode(['error' => '文件上传失败']);
    }
} else {
    echo json_encode(['error' => '没有文件被上传']);
}
?>