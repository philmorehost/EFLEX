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
// ... (same cart fetching logic as before) ...
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
            $cart_items[$product_id] = ['name' => $row['name'], 'price' => $row['price']];
        }
        $stmt->close();
    }
}


// --- Order processing logic ---
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

        $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_items = $mysqli->prepare($sql_items);
        foreach($_SESSION['cart'] as $product_id => $quantity){
            $price = $cart_items[$product_id]['price'];
            $stmt_items->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_items->execute();
        }

        $mysqli->commit();
        unset($_SESSION['cart']);

        if($payment_method === 'bank_transfer'){
            // Redirect to a page with bank details
            header("location: order_details_bank.php?id=" . $order_id);
        } else {
            // Redirect to a generic success page (or later, to Paystack)
            header("location: order_success.php?id=" . $order_id);
        }
        exit();

    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        die('Order failed. Please try again.');
    }
}

// Include the header
include 'includes/header.php';
?>

<h2>Checkout</h2>
<div class="row">
    <!-- Order Summary -->
    <div class="col-md-5 col-lg-4 order-md-last">
        <!-- ... (same order summary HTML as before) ... -->
    </div>

    <!-- Shipping and Payment Form -->
    <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Shipping & Payment</h4>
        <form action="checkout.php" method="post">
            <!-- Shipping Address -->
            <h5 class="mb-3">Shipping address</h5>
            <div class="row g-3">
                <div class="col-12"><label for="fullName" class="form-label">Full name</label><input type="text" class="form-control" name="fullName" required></div>
                <div class="col-12"><label for="address" class="form-label">Address</label><input type="text" class="form-control" name="address" required></div>
            </div>
            <hr class="my-4">

            <!-- Payment Method -->
            <h5 class="mb-3">Payment Method</h5>
            <div class="my-3">
                <div class="form-check">
                    <input id="bank_transfer" name="payment_method" type="radio" class="form-check-input" value="bank_transfer" required checked>
                    <label class="form-check-label" for="bank_transfer">Bank Transfer</label>
                </div>
                <div class="form-check">
                    <input id="paystack" name="payment_method" type="radio" class="form-check-input" value="paystack" disabled>
                    <label class="form-check-label" for="paystack">Paystack (Card, etc.) - Coming Soon</label>
                </div>
            </div>

            <hr class="my-4">

            <button class="w-100 btn btn-primary btn-lg" type="submit" name="place_order">Place Order</button>
        </form>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
