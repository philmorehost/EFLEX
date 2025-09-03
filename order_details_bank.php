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

// Include database connection
require_once 'includes/db_connect.php';

// Check if an order ID is provided from the session, which is set during checkout.
if(!isset($_SESSION['latest_order_id'])){
    // If not, maybe they came from a direct link, check GET
    if(!isset($_GET['id']) || empty($_GET['id'])){
        header("location: my_orders.php");
        exit;
    }
    $order_id = $_GET['id'];
} else {
    $order_id = $_SESSION['latest_order_id'];
    // Unset the session variable so it's not reused accidentally
    unset($_SESSION['latest_order_id']);
}

$user_id = $_SESSION['id'];

// Fetch order details and verify ownership
$sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order = null;
if($stmt_order = $mysqli->prepare($sql_order)){
    $stmt_order->bind_param("ii", $order_id, $user_id);
    $stmt_order->execute();
    $result_order = $stmt_order->get_result();
    if($result_order->num_rows != 1){
        header("location: my_orders.php");
        exit;
    }
    $order = $result_order->fetch_assoc();
    $stmt_order->close();
}

// Fetch bank details from settings
$settings_sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'bank_%'";
$result = $mysqli->query($settings_sql);
$bank_settings = [];
while($row = $result->fetch_assoc()){
    $bank_settings[$row['setting_key']] = $row['setting_value'];
}

// Include the header
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="alert alert-success">
                <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Order Placed Successfully!</h4>
                <p class="mb-0">Your order #<?php echo htmlspecialchars($order_id); ?> is confirmed and awaiting payment.</p>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4><i class="fas fa-university"></i> Bank Transfer Instructions</h4>
                </div>
                <div class="card-body">
                    <p>Please make a payment of <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong> to the following bank account:</p>
                    <ul class="list-group list-group-flush text-start">
                        <li class="list-group-item"><strong>Bank Name:</strong> <?php echo htmlspecialchars($bank_settings['bank_name'] ?? 'Not Specified'); ?></li>
                        <li class="list-group-item"><strong>Account Name:</strong> <?php echo htmlspecialchars($bank_settings['bank_account_name'] ?? 'Not Specified'); ?></li>
                        <li class="list-group-item"><strong>Account Number:</strong> <?php echo htmlspecialchars($bank_settings['bank_account_number'] ?? 'Not Specified'); ?></li>
                    </ul>
                    <hr>
                    <h5><strong>Important Instructions:</strong></h5>
                    <p class="text-muted">
                        <?php echo nl2br(htmlspecialchars($bank_settings['bank_payment_instructions'] ?? 'Please use your Order ID as the payment reference.')); ?>
                    </p>
                </div>
                <div class="card-footer">
                     <p>Once you have made the payment, please go to your "My Orders" page and upload the proof of payment.</p>
                     <a href="my_orders.php" class="btn btn-primary"><i class="fas fa-tasks"></i> Go to My Orders</a>
                     <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
