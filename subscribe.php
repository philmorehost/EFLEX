<?php
// Initialize session and check login status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include header
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center border-primary">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-exclamation-circle"></i> Subscription Required</h2>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Access to Lesson Notes is Restricted</h5>
                    <p class="card-text">We're glad you're interested in our lesson notes! Access to this content is available to users with an active subscription.</p>
                    <hr>
                    <h5>How to Subscribe</h5>
                    <p>To activate your subscription, please follow these steps:</p>
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item">Contact our administration team to arrange payment.</li>
                        <li class="list-group-item">Email: <strong>admin@notes.4myresearch.com</strong></li>
                        <li class="list-group-item">Once your payment is confirmed, an administrator will activate your account.</li>
                    </ol>
                    <a href="index.php" class="btn btn-primary mt-4">Go to Homepage</a>
                </div>
                <div class="card-footer text-muted">
                    Thank you for your understanding.
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
