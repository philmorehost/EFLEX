<?php
// We need to start the session on all pages to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if an order ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: index.php");
    exit;
}

$order_id = $_GET['id'];

// Include the header
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="alert alert-success text-center">
        <h4 class="alert-heading">Thank You!</h4>
        <p>Your order has been placed successfully.</p>
        <hr>
        <p class="mb-0">Your Order ID is: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
    </div>

    <div class="text-center">
        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
    </div>

    <!-- Optional: Display order summary. For now, a simple message is enough. -->
    <!-- A full summary would require fetching the order details again from the DB. -->

</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
