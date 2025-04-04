<?php
// 引入配置文件
require_once 'config.php';

/**
 * 获取所有已发布的文章
 */
function get_published_posts($limit = 10, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, u.username, u.avatar 
                          FROM posts p 
                          JOIN users u ON p.author_id = u.id 
                          WHERE p.is_published = TRUE 
                          ORDER BY p.published_at DESC 
                          LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 获取单篇文章
 */
function get_post($slug) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, u.username, u.avatar, u.bio 
                          FROM posts p 
                          JOIN users u ON p.author_id = u.id 
                          WHERE p.slug = :slug AND p.is_published = TRUE");
    $stmt->bindParam(':slug', $slug);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * 获取文章分类
 */
function get_post_categories($post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT c.* 
                          FROM categories c 
                          JOIN post_categories pc ON c.id = pc.category_id 
                          WHERE pc.post_id = :post_id");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 获取文章标签
 */
function get_post_tags($post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT t.* 
                          FROM tags t 
                          JOIN post_tags pt ON t.id = pt.tag_id 
                          WHERE pt.post_id = :post_id");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 获取分类下的文章
 */
function get_posts_by_category($category_slug, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, u.username, u.avatar 
                          FROM posts p 
                          JOIN users u ON p.author_id = u.id 
                          JOIN post_categories pc ON p.id = pc.post_id 
                          JOIN categories c ON pc.category_id = c.id 
                          WHERE c.slug = :category_slug AND p.is_published = TRUE 
                          ORDER BY p.published_at DESC 
                          LIMIT :limit");
    $stmt->bindParam(':category_slug', $category_slug);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 搜索文章
 */
function search_posts($query, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, u.username, u.avatar 
                          FROM posts p 
                          JOIN users u ON p.author_id = u.id 
                          WHERE (p.title LIKE :query OR p.content LIKE :query) 
                          AND p.is_published = TRUE 
                          ORDER BY p.published_at DESC 
                          LIMIT :limit");
    $search_query = "%$query%";
    $stmt->bindParam(':query', $search_query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 获取所有分类
 */
function get_all_categories() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 获取热门标签
 */
function get_popular_tags($limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT t.*, COUNT(pt.tag_id) as count 
                          FROM tags t 
                          JOIN post_tags pt ON t.id = pt.tag_id 
                          GROUP BY t.id 
                          ORDER BY count DESC 
                          LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 用户登录验证
 */
function login_user($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

/**
* 检查用户是否登录 
*/
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
 
/**
* 检查是否是管理员 
*/
function is_admin() {
    return is_logged_in() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}
 
/**
* 获取当前登录用户信息 
*/
function get_logged_in_user() {
    if (!is_logged_in()) return null;
 
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
 
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Markdown内容转换
 */
function markdown_to_html($markdown) {
    // 简单实现，实际项目中可以使用Parsedown等库
    $html = htmlspecialchars($markdown);
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
    $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $html);
    $html = preg_replace('/`(.*?)`/', '<code>$1</code>', $html);
    $html = preg_replace('/\n\n/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';
    
    return $html;
}

/**
 * 生成SEO友好的URL
 */
function generate_slug($string) {
    $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower($string));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>