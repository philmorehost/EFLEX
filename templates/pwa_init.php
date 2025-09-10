<?php
// templates/pwa_init.php
// This file contains all necessary components for PWA installation.
// It should be included at the end of the body of the target page (e.g., login.php).
?>

<!-- PWA Install Modal -->
<div class="modal fade" id="installPwaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Install CBT Platform App</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>For a better experience, install the CBT Platform app on your device. It's fast, reliable, and works offline.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Not Now</button>
                <button type="button" class="btn btn-primary" id="installPwaBtn">Install</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS is required for the modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo BASE_URL; ?>/sw.js')
                .then(reg => console.log('Service worker registered.', reg))
                .catch(err => console.log('Service worker not registered.', err));
        });
    }

    // PWA Install Prompt Logic
    let deferredPrompt;
    const installModalEl = document.getElementById('installPwaModal');
    const installButton = document.getElementById('installPwaBtn');

    if (installModalEl && typeof bootstrap !== 'undefined') {
        const installModal = new bootstrap.Modal(installModalEl);

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installModal.show();
        });

        if(installButton) {
            installButton.addEventListener('click', async () => {
                installModal.hide();
                if(deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`User response to the install prompt: ${outcome}`);
                    deferredPrompt = null;
                }
            });
        }
    } else {
        console.error("PWA Install prompt cannot be initialized. Bootstrap modal element not found or Bootstrap JS not loaded.");
    }
});
</script>
