<?php
// templates/footer.php
?>
    <!-- Main page content ends here -->
</div>

<!-- Bootstrap 5 JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript to toggle the sidebar
    document.addEventListener("DOMContentLoaded", function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        }
    });
</script>
</body>
</html>
