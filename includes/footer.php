</div> <!-- /container -->

<footer class="site-footer bg-dark text-white pt-5 pb-4">
    <div class="container text-center text-md-start">
        <div class="row">
            <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold">Eflex</h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
                <p>
                    Your one-stop shop for the best products at the best prices. We are committed to providing quality and value.
                </p>
            </div>

            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold">Products</h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
                <p><a href="products.php" class="text-white">All Products</a></p>
                <!-- You can dynamically list categories here if you want -->
            </div>

            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold">Useful links</h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
                <p><a href="account.php" class="text-white">Your Account</a></p>
                <p><a href="my_orders.php" class="text-white">My Orders</a></p>
                <p><a href="#" class="text-white">Shipping Rates</a></p>
                <p><a href="#" class="text-white">Help</a></p>
            </div>

            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                <h6 class="text-uppercase fw-bold">Contact</h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
                <p><i class="fas fa-home me-3"></i> New York, NY 10012, US</p>
                <p><i class="fas fa-envelope me-3"></i> info@eflex.com</p>
                <p><i class="fas fa-phone me-3"></i> + 01 234 567 88</p>
            </div>
        </div>
    </div>
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
        <?php echo $settings['copyright_text'] ?? ('© ' . date("Y") . ' Copyright: <a class="text-white" href="index.php">Eflex.com</a>'); ?>
    </div>
</footer>

<!-- PWA Install Modal -->
<div class="modal fade" id="installPwaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Install Eflex App</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>For a better experience, install the Eflex web app on your device. It's fast, reliable, and works offline!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Later</button>
        <button type="button" class="btn btn-primary" id="installPwaBtn">Install</button>
      </div>
    </div>
  </div>
</div>


<!-- Bootstrap JS Bundle with Popper -->
<script src="js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="js/main.js"></script>

<!-- PWA Registration -->
<?php if($pwa_enabled): ?>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
        .then(() => console.log('Service Worker Registered'))
        .catch(error => console.log('Service Worker registration failed:', error));
    }
</script>
<script src="js/install-pwa.js"></script>
<?php endif; ?>

</body>
</html>
