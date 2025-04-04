    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>关于我们</h3>
                    <p><?php echo SITE_DESCRIPTION; ?></p>
                </div>
                <div class="footer-section">
                    <h3>快速链接</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>">首页</a></li>
                        <?php foreach (get_all_categories() as $category): ?>
                        <li><a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>"><?php echo $category['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>联系我</h3>
                    <ul class="social-links">
                        <li><a href="#"><i class="fab fa-github"></i> GitHub</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> Email</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?>. 保留所有权利.</p>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>