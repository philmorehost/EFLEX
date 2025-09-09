<?php
// We need to start the session on all pages to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    $_SESSION['redirect_to'] = 'checkout.php';
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/db_connect.php';

// Check if the cart is empty, if so, redirect to cart page
if(empty($_SESSION['cart'])){
    header("location: cart.php");
    exit;
}

// Fetch cart items and calculate total price
$cart_items = [];
$total_price = 0;
$product_details_for_cart = [];
if(!empty($_SESSION['cart'])){
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
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
            $cart_items[$product_id] = ['name' => $row['name'], 'price' => $row['price'], 'quantity' => $quantity];
            $product_details_for_cart[$product_id] = $row;
        }
        $stmt->close();
    }
}


// --- Order processing logic ---
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])){
    $user_id = $_SESSION['id'];
    $_SESSION['email'] = $_SESSION['email'] ?? 'customer@example.com';

    if ($total_price == 0) {
        // --- Handle Freemium (Zero-Cost) Orders ---
        $mysqli->begin_transaction();
        try {
            $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method, status) VALUES (?, 0.00, 'freemium', 'Completed')";
            $stmt_order = $mysqli->prepare($sql_order);
            $stmt_order->bind_param("i", $user_id);
            $stmt_order->execute();
            $order_id = $mysqli->insert_id;
            $stmt_order->close();

            $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_items = $mysqli->prepare($sql_items);
            foreach($_SESSION['cart'] as $product_id => $quantity){
                $price = $cart_items[$product_id]['price'];
                $stmt_items->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                $stmt_items->execute();
            }
            $stmt_items->close();

            foreach($_SESSION['cart'] as $product_id => $quantity){
                $duration_days = (int)$product_details_for_cart[$product_id]['duration_days'];
                $expires_at = $duration_days > 0 ? date('Y-m-d H:i:s', strtotime("+$duration_days days")) : null;
                $sql_insert_sub = "INSERT INTO user_subscriptions (user_id, product_id, order_id, status, expires_at) VALUES (?, ?, ?, 'active', ?)";
                $stmt_insert = $mysqli->prepare($sql_insert_sub);
                $stmt_insert->bind_param("iiis", $user_id, $product_id, $order_id, $expires_at);
                $stmt_insert->execute();
                $stmt_insert->close();
            }

            $mysqli->commit();
            unset($_SESSION['cart']);
            $_SESSION['freemium_success'] = true;
            header("location: order_success.php?order_id=" . $order_id);
            exit();

        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            die('Freemium order failed. Please try again. ' . $exception->getMessage());
        }
    } else {
        // --- Handle Paid Orders ---
        $payment_method = $_POST['payment_method'];
        $status = ($payment_method === 'bank_transfer') ? 'Awaiting Payment' : 'Pending';
        $_SESSION['total_amount'] = $total_price;

        // Store customer details in session for the payment handler
        $_SESSION['checkout_details'] = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        $shipping_address = trim($_POST['first_name'] . ' ' . $_POST['last_name'] . "\n" . $_POST['address']);

        $mysqli->begin_transaction();
        try {
            $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method, status, shipping_address) VALUES (?, ?, ?, ?, ?)";
            $stmt_order = $mysqli->prepare($sql_order);
            $stmt_order->bind_param("idsss", $user_id, $total_price, $payment_method, $status, $shipping_address);
            $stmt_order->execute();
            $order_id = $mysqli->insert_id;
            $_SESSION['latest_order_id'] = $order_id;

            $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_items = $mysqli->prepare($sql_items);
            foreach($_SESSION['cart'] as $product_id => $quantity){
                $price = $cart_items[$product_id]['price'];
                $stmt_items->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                $stmt_items->execute();
            }
            $stmt_items->close();

            $mysqli->commit();

            if($payment_method === 'bank_transfer'){
                header("location: order_details_bank.php");
            } elseif ($payment_method === 'paystack') {
                header("location: paystack_handler.php");
            }
            exit();

        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            die('Order failed. Please try again. ' . $exception->getMessage());
        }
    }
}

// Include the header
include 'includes/header.php';
?>

<h2>Checkout</h2>
<div class="row">
    <div class="col-md-5 col-lg-4 order-md-last">
        <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Your Classes</span>
            <span class="badge bg-primary rounded-pill"><?php echo count($cart_items); ?></span>
        </h4>
        <ul class="list-group mb-3">
            <?php foreach($cart_items as $item): ?>
            <li class="list-group-item d-flex justify-content-between lh-sm">
                <div>
                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                    <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                </div>
                <span class="text-muted"><?php echo format_price($item['price'] * $item['quantity']); ?></span>
            </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between">
                <span>Total</span>
                <strong><?php echo format_price($total_price); ?></strong>
            </li>
        </ul>
    </div>

    <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Your Details</h4>
        <form action="checkout.php" method="post" id="checkout-form">
            <?php if($total_price > 0): ?>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="first_name" class="form-label">First name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="" value="" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="last_name" class="form-label">Last name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="" value="" required>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="1234 Main St" required>
                    </div>
                </div>
                <hr class="my-4">
                <h5 class="mb-3">Payment Method</h5>
            <?php else: ?>
                 <p>Your subscription will be linked to your account: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
            <?php endif; ?>

            <?php if($total_price > 0): ?>
                <div class="my-3">
                    <div class="form-check">
                        <input id="bank_transfer" name="payment_method" type="radio" class="form-check-input" value="bank_transfer" required checked>
                        <label class="form-check-label" for="bank_transfer">Bank Transfer</label>
                    </div>
                     <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="paystack" value="paystack" required>
                        <label class="form-check-label" for="paystack">
                            Pay with Paystack (Credit/Debit Card)
                        </label>
                    </div>
                </div>
                <hr class="my-4">
                <button class="w-100 btn btn-primary btn-lg" type="submit" name="place_order">Place Order</button>
            <?php else: ?>
                <div class="alert alert-info">This is a free subscription. No payment is required.</div>
                <button class="w-100 btn btn-primary btn-lg" type="submit" name="place_order">Get Freemium Access</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
