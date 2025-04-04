<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

$user = get_logged_in_user();
$categories = get_all_categories();
$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = trim($_POST['content']);
    $excerpt = trim($_POST['excerpt']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $published_at = $is_published ? (trim($_POST['published_at']) ?: date('Y-m-d H:i:s')) : null;
    $selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    $cover_image = trim($_POST['cover_image']);

    // 验证数据
    $errors = [];
    
    if (empty($title)) {
        $errors[] = '标题不能为空';
    }
    
    if (empty($slug)) {
        $slug = generate_slug($title);
    } else {
        $slug = generate_slug($slug);
    }
    
    // 检查slug是否唯一
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = :slug AND id != :id");
    $stmt->bindParam(':slug', $slug);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        $errors[] = 'URL别名已被使用，请换一个';
    }
    
    if (empty($content)) {
        $errors[] = '内容不能为空';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            if ($id > 0) {
                // 更新文章
                $stmt = $pdo->prepare("UPDATE posts SET 
                                      title = :title, 
                                      slug = :slug, 
                                      content = :content, 
                                      excerpt = :excerpt, 
                                      cover_image = :cover_image, 
                                      is_published = :is_published, 
                                      published_at = :published_at, 
                                      updated_at = NOW() 
                                      WHERE id = :id");
                $stmt->bindParam(':id', $id);
            } else {
                // 新增文章
                $stmt = $pdo->prepare("INSERT INTO posts 
                                      (title, slug, content, excerpt, cover_image, is_published, published_at, author_id, created_at, updated_at) 
                                      VALUES 
                                      (:title, :slug, :content, :excerpt, :cover_image, :is_published, :published_at, :author_id, NOW(), NOW())");
                $stmt->bindParam(':author_id', $user['id']);
            }
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':excerpt', $excerpt);
            $stmt->bindParam(':cover_image', $cover_image);
            $stmt->bindParam(':is_published', $is_published, PDO::PARAM_BOOL);
            $stmt->bindParam(':published_at', $published_at);
            $stmt->execute();
            
            if ($id === 0) {
                $id = $pdo->lastInsertId();
            }
            
            // 更新分类关联
            $pdo->exec("DELETE FROM post_categories WHERE post_id = $id");
            if (!empty($selected_categories)) {
                $stmt = $pdo->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)");
                $stmt->bindParam(':post_id', $id);
                foreach ($selected_categories as $category_id) {
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->execute();
                }
            }
            
            // 更新标签关联
            $pdo->exec("DELETE FROM post_tags WHERE post_id = $id");
            if (!empty($selected_tags)) {
                $stmt = $pdo->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)");
                $stmt->bindParam(':post_id', $id);
                foreach ($selected_tags as $tag_id) {
                    $stmt->bindParam(':tag_id', $tag_id);
                    $stmt->execute();
                }
            }
            
            $pdo->commit();
            
            $_SESSION['success_message'] = $id > 0 ? '文章更新成功' : '文章创建成功';
            header("Location: posts.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = '数据库错误: ' . $e->getMessage();
        }
    }
}

// 获取编辑的文章数据
$post = null;
$post_categories = [];
$post_tags = [];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        // 获取文章分类
        $stmt = $pdo->prepare("SELECT category_id FROM post_categories WHERE post_id = :post_id");
        $stmt->bindParam(':post_id', $id);
        $stmt->execute();
        $post_categories = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // 获取文章标签
        $stmt = $pdo->prepare("SELECT tag_id FROM post_tags WHERE post_id = :post_id");
        $stmt->bindParam(':post_id', $id);
        $stmt->execute();
        $post_tags = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}

$page_title = ($post ? '编辑文章' : '新增文章') . ' - ' . SITE_TITLE;
require_once 'includes/admin-header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/admin-sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <h1><?php echo $post ? '编辑文章' : '新增文章'; ?></h1>
            <a href="posts.php" class="btn btn-back">返回列表</a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="post-form">
            <input type="hidden" name="id" value="<?php echo $post ? $post['id'] : 0; ?>">
            
            <div class="form-group">
                <label for="title">标题 *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post ? $post['title'] : ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="slug">URL别名</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post ? $post['slug'] : ''); ?>">
                <p class="form-hint">如果不填，将自动从标题生成</p>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cover_image">封面图URL</label>
                    <input type="text" id="cover_image" name="cover_image" value="<?php echo htmlspecialchars($post ? $post['cover_image'] : ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="published_at">发布时间</label>
                    <input type="datetime-local" id="published_at" name="published_at" 
                           value="<?php echo $post && $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i'); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="excerpt">摘要</label>
                <textarea id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($post ? $post['excerpt'] : ''); ?></textarea>
                <p class="form-hint">如果不填，将从内容自动提取前150个字符</p>
            </div>
            
            <div class="form-group">
                <label for="content">内容 *</label>
                <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($post ? $post['content'] : ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>分类</label>
                    <div class="checkbox-group">
                        <?php foreach ($categories as $category): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>"
                                    <?php echo in_array($category['id'], $post_categories) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>标签</label>
                    <div class="checkbox-group">
                        <?php foreach ($tags as $tag): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                    <?php echo in_array($tag['id'], $post_tags) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_published" value="1" <?php echo $post && $post['is_published'] ? 'checked' : ''; ?>>
                    发布这篇文章
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="posts.php" class="btn btn-cancel">取消</a>
                <?php if ($post): ?>
                    <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn btn-delete" onclick="return confirm('确定要删除这篇文章吗？');">删除</a>
                <?php endif; ?>
            </div>
        </form>
    </main>
</div>

<script>
// 自动生成slug
document.getElementById('title').addEventListener('blur', function() {
    if (!document.getElementById('slug').value) {
        fetch('../includes/generate-slug.php?text=' + encodeURIComponent(this.value))
            .then(response => response.text())
            .then(slug => {
                document.getElementById('slug').value = slug;
            });
    }
});
</script>

<?php
require_once 'includes/admin-footer.php';
?>