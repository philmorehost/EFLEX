</div> <!-- /.container -->

<footer class="mt-5 py-4 bg-light">
    <div class="container text-center">
        <span class="text-muted">Copyright &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(get_setting('site_title') ?: 'Marketplace'); ?></span>
    </div>
</footer>

<!-- PWA Install Modal -->
<div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pwaInstallModalLabel">Install Our App</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>For a better experience, install our app on your device. It's fast, and you can use it offline!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Not Now</button>
        <button type="button" class="btn btn-primary" id="pwa-install-button">Install</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // PWA Installation Logic
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js')
                .then(registration => {
                    console.log('Service Worker registered with scope:', registration.scope);
                }).catch(error => {
                    console.log('Service Worker registration failed:', error);
                });
        });
    }

    let deferredPrompt;
    const pwaInstallModalEl = document.getElementById('pwaInstallModal');
    const pwaInstallModal = pwaInstallModalEl ? new bootstrap.Modal(pwaInstallModalEl) : null;
    const installButton = document.getElementById('pwa-install-button');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;
        // Show the install modal
        if(pwaInstallModal) {
            pwaInstallModal.show();
        }
    });

    if (installButton) {
        installButton.addEventListener('click', async () => {
            if (pwaInstallModal) {
                pwaInstallModal.hide();
            }
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                deferredPrompt = null;
            }
        });
    }
</script>
</body>
</html>