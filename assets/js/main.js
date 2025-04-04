// 菜单切换 
document.addEventListener('DOMContentLoaded',  function() {
    const menuToggle = document.querySelector('.menu-toggle'); 
    const mainNav = document.querySelector('.main-nav'); 
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click',  function() {
            mainNav.classList.toggle('active'); 
        });
    }
    
    // 主题切换 
    const themeToggle = document.getElementById('theme-toggle'); 
    if (themeToggle) {
        themeToggle.addEventListener('click',  function() {
            const currentTheme = document.documentElement.getAttribute('data-theme'); 
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme',  newTheme);
            localStorage.setItem('theme',  newTheme);
            themeToggle.innerHTML  = newTheme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
        
        // 检查本地存储中的主题设置 
        const savedTheme = localStorage.getItem('theme'); 
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme',  savedTheme);
            themeToggle.innerHTML  = savedTheme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        }
    }
    
    // 图片懒加载 
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]'); 
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry)  => {
                if (entry.isIntersecting)  {
                    const img = entry.target; 
                    img.src  = img.getAttribute('data-src'); 
                    img.removeAttribute('data-src'); 
                    imageObserver.unobserve(img); 
                }
            });
        });
        
        lazyImages.forEach((img)  => {
            imageObserver.observe(img); 
        });
    }
});