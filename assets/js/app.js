// Custom JavaScript for the CBT Platform

$(document).ready(function() {
    console.log("CBT Platform is ready!");

    // --- Admin Sidebar Toggle Logic ---
    var menuToggle = document.getElementById("menu-toggle");
    if (menuToggle) {
        menuToggle.addEventListener("click", function() {
            var adminWrapper = document.getElementById("admin-wrapper");
            if (adminWrapper) {
                adminWrapper.classList.toggle("toggled");
            }
        });
    }

    // --- PWA Install Prompt Logic ---
    let deferredPrompt;
    const installModal = new bootstrap.Modal(document.getElementById('pwaInstallModal'));
    const installButton = document.getElementById('pwa-install-button');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;

        // Check if the user has already been prompted in this session
        if (!sessionStorage.getItem('pwaPromptShown')) {
            // Show the custom install prompt modal
            installModal.show();
            sessionStorage.setItem('pwaPromptShown', 'true');
        }
    });

    if(installButton) {
        installButton.addEventListener('click', async () => {
            // Hide the modal
            installModal.hide();
            // Show the install prompt
            if (deferredPrompt) {
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                // We've used the prompt, and can't use it again, throw it away
                deferredPrompt = null;
            }
        });
    }

    window.addEventListener('appinstalled', () => {
        // Hide the install prompt modal if it's open
        installModal.hide();
        // Clear the deferredPrompt so it can be garbage collected
        deferredPrompt = null;
        console.log('PWA was installed');
    });
});
