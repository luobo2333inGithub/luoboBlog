document.addEventListener('DOMContentLoaded',  function() {
    // 表单验证 
    const forms = document.querySelectorAll('form'); 
    forms.forEach(form  => {
        form.addEventListener('submit',  function(e) {
            const requiredFields = form.querySelectorAll('[required]'); 
            let isValid = true;
            
            requiredFields.forEach(field  => {
                if (!field.value.trim())  {
                    isValid = false;
                    field.style.borderColor  = 'var(--danger-color)';
                } else {
                    field.style.borderColor  = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault(); 
                alert('请填写所有必填字段');
            }
        });
    });
    
    // 确认对话框 
    const deleteButtons = document.querySelectorAll('.btn-delete,  a[onclick*="confirm"]');
    deleteButtons.forEach(button  => {
        button.addEventListener('click',  function(e) {
            if (!confirm('确定要执行此操作吗？')) {
                e.preventDefault(); 
            }
        });
    });
    
    // 富文本编辑器初始化 
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('content',  {
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Blockquote'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                { name: 'styles', items: ['Styles', 'Format'] },
                { name: 'document', items: ['Source'] }
            ],
            height: 400,
            removeButtons: '',
            filebrowserUploadUrl: '../includes/upload.php' 
        });
    }
    
    // 日期时间选择器 
    if (typeof flatpickr !== 'undefined') {
        flatpickr('[type="datetime-local"]', {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true 
        });
    }
    
    // 标签输入 
    const tagInputs = document.querySelectorAll('.tag-input'); 
    tagInputs.forEach(input  => {
        new Tagify(input, {
            whitelist: JSON.parse(input.dataset.tags), 
            dropdown: {
                enabled: 0,
                maxItems: 10 
            }
        });
    });
});