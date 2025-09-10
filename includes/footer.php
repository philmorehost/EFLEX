</div> <!-- /container -->

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <h5><?php echo get_app_setting('site_title', 'Eflex'); ?></h5>
                <p><?php echo get_app_setting('site_info', 'Your one-stop shop for high-quality educational materials. We are committed to providing an excellent learning experience.'); ?></p>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Classes</a></li>
                     <li><a href="subscriptions.php">Subscriptions</a></li>
                    <li><a href="account.php">My Account</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>Contact Us</h5>
                <ul class="list-unstyled">
                    <?php if ($address = get_app_setting('contact_address')): ?>
                        <li><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($address); ?></li>
                    <?php endif; ?>
                    <?php if ($phone = get_app_setting('contact_phone')): ?>
                        <li><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($phone); ?></li>
                    <?php endif; ?>
                    <?php if ($email = get_app_setting('contact_email')): ?>
                        <li><i class="fas fa-envelope me-2"></i><a href="mailto:<?php echo htmlspecialchars($email); ?>"><?php echo htmlspecialchars($email); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>Follow Us</h5>
                <?php if ($facebook_url = get_app_setting('social_facebook')): ?>
                    <a href="<?php echo htmlspecialchars($facebook_url); ?>" class="btn btn-outline-light btn-floating m-1" role="button" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if ($twitter_url = get_app_setting('social_twitter')): ?>
                    <a href="<?php echo htmlspecialchars($twitter_url); ?>" class="btn btn-outline-light btn-floating m-1" role="button" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                <?php endif; ?>
                <?php if ($instagram_url = get_app_setting('social_instagram')): ?>
                    <a href="<?php echo htmlspecialchars($instagram_url); ?>" class="btn btn-outline-light btn-floating m-1" role="button" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                 <?php if ($linkedin_url = get_app_setting('social_linkedin')): ?>
                    <a href="<?php echo htmlspecialchars($linkedin_url); ?>" class="btn btn-outline-light btn-floating m-1" role="button" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p><?php echo get_app_setting('footer_copyright_text', '&copy; ' . date("Y") . ' ' . get_app_setting('site_title', 'Eflex') . '. All Rights Reserved.'); ?></p>
        </div>
    </div>
</footer>

<!-- PWA Install Prompt Modal -->
<div class="modal fade" id="pwa-install-modal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pwaInstallModalLabel">Install Our App</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="pwa-dismiss-button"></button>
            </div>
            <div class="modal-body">
                <p>For a better experience, install our app on your device. It's fast, reliable, and you can use it offline.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Not Now</button>
                <button type="button" class="btn btn-primary" id="pwa-install-button">Install</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="js/main.js"></script>

</body>
</html>
