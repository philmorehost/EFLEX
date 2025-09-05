</div> <!-- closing container-fluid -->
</div> <!-- closing content-wrapper -->

<!-- Bootstrap JS Bundle with Popper -->
<script src="<?php echo $base_url; ?>js/bootstrap.bundle.min.js"></script>
<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    // Initialize CKEditor on any page that has an element with the id 'description'
    const editorElement = document.querySelector('#description');
    if (editorElement) {
        ClassicEditor
            .create(editorElement)
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
            });
    }
</script>
</body>
</html>
