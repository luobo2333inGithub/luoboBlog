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
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug");
    $stmt->bindParam(':slug', $slug);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        $errors[] = 'URL别名已被使用，请换一个';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
            
            $_SESSION['success_message'] = '分类添加成功';
            header("Location: categories.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = '数据库错误: ' . $e->getMessage();
        }
    }
}

// 获取所有分类
$categories = $pdo->query("SELECT c.*, COUNT(pc.post_id) as post_count 
                          FROM categories c 
                          LEFT JOIN post_categories pc ON c.id = pc.category_id 
                          GROUP BY c.id 
                          ORDER BY c.name ASC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "分类管理 - " . SITE_TITLE;
require_once 'includes/admin-header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/admin-sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <h1>分类管理</h1>
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
        
        <div class="two-column-layout">
            <div class="column">
                <h2>添加新分类</h2>
                <form method="POST" class="category-form">
                    <div class="form-group">
                        <label for="name">名称 *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="slug">URL别名</label>
                        <input type="text" id="slug" name="slug">
                        <p class="form-hint">如果不填，将自动从名称生成</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">描述</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">添加分类</button>
                    </div>
                </form>
            </div>
            
            <div class="column">
                <h2>现有分类</h2>
                <?php if (!empty($categories)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>名称</th>
                                <th>文章数</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <a href="edit-category.php?id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                                        <div class="slug"><?php echo $category['slug']; ?></div>
                                    </td>
                                    <td><?php echo $category['post_count']; ?></td>
                                    <td class="actions">
                                        <a href="edit-category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-edit"><i class="fas fa-edit"></i> 编辑</a>
                                        <?php if ($category['post_count'] == 0): ?>
                                            <a href="delete-category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('确定要删除这个分类吗？');"><i class="fas fa-trash"></i> 删除</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">暂无分类</p>
                <?php endif; ?>
            </div>
        </div>
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