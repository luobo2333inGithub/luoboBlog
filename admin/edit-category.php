<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// 获取分类ID
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit;
}

$id = intval($_GET['id']);

// 获取分类数据
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: categories.php");
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = '分类名称不能为空';
    }
    
    if (empty($slug)) {
        $slug = generate_slug($name);
    } else {
        $slug = generate_slug($slug);
    }
    
    // 检查slug是否唯一
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug AND id != :id");
    $stmt->bindParam(':slug', $slug);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        $errors[] = 'URL别名已被使用，请换一个';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET 
                                 name = :name, 
                                 slug = :slug, 
                                 description = :description 
                                 WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $_SESSION['success_message'] = '分类更新成功';
            header("Location: categories.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = '数据库错误: ' . $e->getMessage();
        }
    }
}

$page_title = "编辑分类 - " . SITE_TITLE;
require_once 'includes/admin-header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/admin-sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <h1>编辑分类</h1>
            <a href="categories.php" class="btn btn-back">返回列表</a>
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
        
        <form method="POST" class="category-form">
            <div class="form-group">
                <label for="name">名称 *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="slug">URL别名</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($category['slug']); ?>">
                <p class="form-hint">如果不填，将自动从名称生成</p>
            </div>
            
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">保存更改</button>
                <a href="categories.php" class="btn btn-cancel">取消</a>
                <a href="delete-category.php?id=<?php echo $category['id']; ?>" class="btn btn-delete" onclick="return confirm('确定要删除这个分类吗？');">删除</a>
            </div>
        </form>
    </main>
</div>

<script>
// 自动生成slug
document.getElementById('name').addEventListener('blur', function() {
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