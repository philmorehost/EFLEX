</div> <!-- .wrapper -->

<!-- Core JS -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom App JS -->
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>

<!-- PWA Service Worker Registration -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo BASE_URL; ?>sw.js')
                .then(registration => {
                    console.log('Service Worker registered successfully:', registration);
                })
                .catch(error => {
                    console.error('Service Worker registration failed:', error);
                });
        });
    }
</script>

</body>
</html>
