<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    $_SESSION['redirect_to'] = 'checkout.php';
    header("location: login.php");
    exit;
}

require_once 'includes/db_connect.php';
require_once 'includes/send_email.php';
require_once 'includes/send_notification.php';

if(empty($_SESSION['cart'])){
    header("location: cart.php");
    exit;
}

// Fetch site settings for payment gateways and email
$settings_sql = "SELECT setting_key, setting_value FROM settings";
$result = $mysqli->query($settings_sql);
$settings = [];
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}
$paystack_enabled = !empty($settings['paystack_enabled']);
$bank_transfer_enabled = !empty($settings['bank_transfer_enabled']);
$stripe_enabled = !empty($settings['stripe_enabled']);
$flutterwave_enabled = !empty($settings['flutterwave_enabled']);
$admin_email = $settings['from_email'] ?? '';
$site_name = $settings['site_name'] ?? 'Eflex';


// Fetch cart items and calculate total
$cart_items = [];
$total_price = 0;
if(!empty($_SESSION['cart'])){
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $sql = "SELECT id, name, price FROM products WHERE id IN ($placeholders)";
    if($stmt = $mysqli->prepare($sql)){
        $types = str_repeat('i', count($product_ids));
        $stmt->bind_param($types, ...$product_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $product_id = $row['id'];
            $quantity = $_SESSION['cart'][$product_id];
            $subtotal = $row['price'] * $quantity;
            $total_price += $subtotal;
            $cart_items[$product_id] = ['name' => $row['name'], 'price' => $row['price']];
        }
        $stmt->close();
    }
}


// Order processing
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])){
    $user_id = $_SESSION['id'];
    $payment_method = $_POST['payment_method'];
    $status = ($payment_method === 'bank_transfer') ? 'Awaiting Payment' : 'Pending';

    $mysqli->begin_transaction();
    try {
        $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method, status) VALUES (?, ?, ?, ?)";
        $stmt_order = $mysqli->prepare($sql_order);
        $stmt_order->bind_param("idss", $user_id, $total_price, $payment_method, $status);
        $stmt_order->execute();
        $order_id = $mysqli->insert_id;
        $_SESSION['order_id'] = $order_id; // Store order_id in session for Paystack

        $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_items = $mysqli->prepare($sql_items);
        foreach($_SESSION['cart'] as $product_id => $quantity){
            $price = $cart_items[$product_id]['price'];
            $stmt_items->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_items->execute();
        }
        $stmt_items->close();
        $stmt_order->close();

        $mysqli->commit();
        unset($_SESSION['cart']);

        // Send confirmation emails
        if(!empty($admin_email)){
            $currency_symbol = htmlspecialchars($_SESSION['currency_symbol']);
            // To User
            $user_email = $_SESSION['email'];
            $user_subject = "Your Order Confirmation from " . $site_name;
            $user_body = "<h1>Thank You for Your Order!</h1>"
                       . "<p>Hi " . $_SESSION['username'] . ",</p>"
                       . "<p>We've received your order (#" . $order_id . ") and are getting it ready.</p>"
                       . "<p><strong>Total:</strong> " . $currency_symbol . number_format($total_price, 2) . "</p>"
                       . "<p><strong>Payment Method:</strong> " . ucfirst($payment_method) . "</p>"
                       . "<p>You can view your order details here: <a href='http://".$_SERVER['HTTP_HOST']."/my_orders.php'>My Orders</a></p>";
            send_email($user_email, $user_subject, $user_body);

            // To Admin
            $admin_subject = "New Order Notification: #" . $order_id;
            $admin_body = "<h1>New Order Received</h1>"
                        . "<p>A new order has been placed on your website.</p>"
                        . "<p><strong>Order ID:</strong> #" . $order_id . "</p>"
                        . "<p><strong>Customer:</strong> " . $_SESSION['username'] . "</p>"
                        . "<p><strong>Total:</strong> " . $currency_symbol . number_format($total_price, 2) . "</p>"
                        . "<p>You can view the full details in the admin panel.</p>";
            send_email($admin_email, $admin_subject, $admin_body);
        }

        // Send push notification to all staff
        $sql_staff_players = "SELECT onesignal_player_id FROM users WHERE role_id IS NOT NULL AND onesignal_player_id IS NOT NULL";
        $result_players = $mysqli->query($sql_staff_players);
        $player_ids = [];
        while($row = $result_players->fetch_assoc()){
            $player_ids[] = $row['onesignal_player_id'];
        }
        if(!empty($player_ids)){
            $currency_symbol = htmlspecialchars($_SESSION['currency_symbol']);
            $push_message = "New order (#" . $order_id . ") placed for " . $currency_symbol . number_format($total_price, 2);
            send_push_notification($player_ids, $push_message, "New Order Received!");
        }


        if($payment_method === 'paystack'){
            header("location: paystack_charge.php");
        } elseif ($payment_method === 'stripe') {
            header("location: stripe_charge.php");
        } elseif ($payment_method === 'flutterwave') {
            header("location: flutterwave_charge.php");
        } elseif ($payment_method === 'bank_transfer') {
             header("location: order_details_bank.php?id=" . $order_id);
        } else {
            header("location: order_success.php?id=" . $order_id);
        }
        exit();

    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        die('Order failed. Please try again. Error: ' . $exception->getMessage());
    }
}

include 'includes/header.php';
?>

<h2>Checkout</h2>
<div class="row g-5">
    <!-- Order Summary -->
    <div class="col-md-5 col-lg-4 order-md-last">
        <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Your cart</span>
            <span class="badge bg-primary rounded-pill"><?php echo count($_SESSION['cart'] ?? []); ?></span>
        </h4>
        <ul class="list-group mb-3">
            <?php foreach($_SESSION['cart'] as $product_id => $quantity): ?>
                 <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0"><?php echo htmlspecialchars($cart_items[$product_id]['name']); ?> (x<?php echo $quantity; ?>)</h6>
                    </div>
                    <span class="text-muted"><?php echo htmlspecialchars($_SESSION['currency_symbol']); ?><?php echo number_format($cart_items[$product_id]['price'] * $quantity, 2); ?></span>
                </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between">
                <span>Total (<?php echo htmlspecialchars($_SESSION['currency_code']); ?>)</span>
                <strong><?php echo htmlspecialchars($_SESSION['currency_symbol']); ?><?php echo number_format($total_price, 2); ?></strong>
            </li>
        </ul>
    </div>

    <!-- Shipping and Payment Form -->
    <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Shipping & Payment</h4>
        <form action="checkout.php" method="post" id="checkout-form">
            <h5 class="mb-3">Shipping address</h5>
            <div class="row g-3">
                 <div class="col-12"><label for="email" class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required></div>
            </div>
            <hr class="my-4">

            <h5 class="mb-3">Payment Method</h5>
            <div class="my-3">
                <?php $is_first = true; ?>
                <?php if($bank_transfer_enabled): ?>
                <div class="form-check">
                    <input id="bank_transfer" name="payment_method" type="radio" class="form-check-input" value="bank_transfer" required <?php if($is_first){ echo 'checked'; $is_first = false; } ?>>
                    <label class="form-check-label" for="bank_transfer">Bank Transfer</label>
                </div>
                <?php endif; ?>
                <?php if($paystack_enabled): ?>
                <div class="form-check">
                    <input id="paystack" name="payment_method" type="radio" class="form-check-input" value="paystack" required <?php if($is_first){ echo 'checked'; $is_first = false; } ?>>
                    <label class="form-check-label" for="paystack">Paystack (Card, Bank, USSD)</label>
                </div>
                <?php endif; ?>
                <?php if($stripe_enabled): ?>
                <div class="form-check">
                    <input id="stripe" name="payment_method" type="radio" class="form-check-input" value="stripe" required <?php if($is_first){ echo 'checked'; $is_first = false; } ?>>
                    <label class="form-check-label" for="stripe">Stripe (Credit/Debit Card)</label>
                </div>
                <?php endif; ?>
                 <?php if($flutterwave_enabled): ?>
                <div class="form-check">
                    <input id="flutterwave" name="payment_method" type="radio" class="form-check-input" value="flutterwave" required <?php if($is_first){ echo 'checked'; $is_first = false; } ?>>
                    <label class="form-check-label" for="flutterwave">Flutterwave (Card, Bank, etc)</label>
                </div>
                <?php endif; ?>
            </div>

            <hr class="my-4">

            <button class="w-100 btn btn-primary btn-lg" type="submit" name="place_order">Place Order</button>
        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
