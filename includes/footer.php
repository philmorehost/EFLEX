</div> <!-- /container -->

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><?php echo htmlspecialchars(get_app_setting('site_title', 'Eflex')); ?></h5>
                <p><?php echo htmlspecialchars(get_app_setting('site_info', 'Your one-stop shop for the latest and greatest products. We are committed to providing high-quality products and an excellent shopping experience.')); ?></p>
            </div>
            <div class="col-md-2 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="account.php">My Account</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-3">
                <h5>Contact Us</h5>
                <ul class="list-unstyled">
                    <?php $contact_email = get_app_setting('contact_email'); if(!empty($contact_email)): ?>
                        <li><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact_email); ?></li>
                    <?php endif; ?>
                    <?php $contact_phone = get_app_setting('contact_phone'); if(!empty($contact_phone)): ?>
                        <li><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact_phone); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3 mb-3">
                <h5>Follow Us</h5>
                <?php $fb_url = get_app_setting('social_facebook'); if(!empty($fb_url)): ?>
                    <a href="<?php echo htmlspecialchars($fb_url); ?>" target="_blank" class="btn btn-outline-light btn-floating m-1" role="button"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php $tw_url = get_app_setting('social_twitter'); if(!empty($tw_url)): ?>
                    <a href="<?php echo htmlspecialchars($tw_url); ?>" target="_blank" class="btn btn-outline-light btn-floating m-1" role="button"><i class="fab fa-twitter"></i></a>
                <?php endif; ?>
                <?php $ig_url = get_app_setting('social_instagram'); if(!empty($ig_url)): ?>
                    <a href="<?php echo htmlspecialchars($ig_url); ?>" target="_blank" class="btn btn-outline-light btn-floating m-1" role="button"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php $li_url = get_app_setting('social_linkedin'); if(!empty($li_url)): ?>
                    <a href="<?php echo htmlspecialchars($li_url); ?>" target="_blank" class="btn btn-outline-light btn-floating m-1" role="button"><i class="fab fa-linkedin-in"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(get_app_setting('site_title', 'Eflex E-commerce')); ?>. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="js/main.js"></script>

</body>
</html>
