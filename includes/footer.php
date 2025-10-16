</div> <!-- .wrapper -->

<footer class="bg-light text-center py-4 mt-auto">
    <div class="container">
        <?php
        // Only show the copyright on the public-facing landing page
        if (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_SESSION['user_id'])) {
            // Self-healing: Check if the copyright setting exists
            $copyright_query = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'copyright_text'");
            if ($copyright_query && $copyright_query->num_rows > 0) {
                $copyright_text = $copyright_query->fetch_assoc()['setting_value'];
            } else {
                // If it doesn't exist, create it with a default value
                $default_copyright = '&copy; ' . date('Y') . ' ' . SITE_NAME . '. All Rights Reserved.';
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('copyright_text', '" . $conn->real_escape_string($default_copyright) . "')");
                $copyright_text = $default_copyright;
            }
            // Use html_entity_decode to render HTML tags correctly
            echo '<p class="text-muted mb-0">' . html_entity_decode($copyright_text) . '</p>';
        }
        ?>
    </div>
</footer>

<!-- PWA Install Modal -->
<div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pwaInstallModalLabel">Install CBT Platform App</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        For a better experience and easy access, install the CBT Platform app on your device. It's fast and uses very little storage.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Later</button>
        <button type="button" class="btn btn-primary" id="pwa-install-button">Install</button>
      </div>
    </div>
  </div>
</div>

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
