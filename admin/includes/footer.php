</div> <!-- closing container-fluid -->
</div> <!-- closing content-wrapper -->

<!-- Bootstrap JS Bundle with Popper -->
<script src="<?php echo $base_url; ?>js/bootstrap.bundle.min.js"></script>
<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    // Initialize CKEditor on any page that has an element with the id 'html_content'
    const editorElement = document.querySelector('#html_content');
    if (editorElement) {
        ClassicEditor
            .create(editorElement)
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
            });
    }

    // Sidebar Toggle Script
    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const adminBody = document.getElementById('admin-body');

    if (sidebarToggleBtn && adminBody) {
        sidebarToggleBtn.addEventListener('click', function() {
            adminBody.classList.toggle('sidebar-toggled');
        });
    }
</script>
</body>
</html>
