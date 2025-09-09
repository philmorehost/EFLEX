</div> <!-- closing container-fluid -->
</div> <!-- closing content-wrapper -->

<div class="overlay" id="overlay"></div>

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

    // Sidebar Toggle Logic
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('overlay');

    if (sidebarToggle && sidebar && overlay) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('is-open');
            overlay.classList.toggle('is-active');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-active');
        });
    }
</script>
</body>
</html>
