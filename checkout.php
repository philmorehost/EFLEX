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

            $cart_items[$product_id] = [ // Use product ID as key for easier access
                'name' => $row['name'],
                'price' => $row['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
        $stmt->close();
    }
}

// --- Order processing logic ---
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])){
    $user_id = $_SESSION['id'];

    // Start transaction
    $mysqli->begin_transaction();

    try {
        // Insert into orders table
        $sql_order = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
        $stmt_order = $mysqli->prepare($sql_order);
        $stmt_order->bind_param("id", $user_id, $total_price);
        $stmt_order->execute();
        $order_id = $mysqli->insert_id; // Get the new order ID

        // Insert into order_items table
        $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_items = $mysqli->prepare($sql_items);
        foreach($_SESSION['cart'] as $product_id => $quantity){
            $price = $cart_items[$product_id]['price'];
            $stmt_items->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_items->execute();
        }

        // If we get here, no errors, so commit the transaction
        $mysqli->commit();

        // Clear the cart
        unset($_SESSION['cart']);

        // Redirect to a success page
        header("location: order_success.php?id=" . $order_id);
        exit();

    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        // You could log the error and show a generic message
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
        <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Your cart</span>
            <span class="badge bg-primary rounded-pill"><?php echo count($cart_items); ?></span>
        </h4>
        <ul class="list-group mb-3">
            <?php foreach($cart_items as $item): ?>
            <li class="list-group-item d-flex justify-content-between lh-sm">
                <div>
                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                    <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                </div>
                <span class="text-muted">$<?php echo number_format($item['subtotal'], 2); ?></span>
            </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between">
                <span>Total (USD)</span>
                <strong>$<?php echo number_format($total_price, 2); ?></strong>
            </li>
        </ul>
    </div>

    <!-- Shipping Information Form -->
    <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Shipping address</h4>
        <!-- The shipping address isn't saved in this version, but the form is here for UI completeness -->
        <form action="checkout.php" method="post">
            <div class="row g-3">
                <div class="col-12">
                    <label for="fullName" class="form-label">Full name</label>
                    <input type="text" class="form-control" id="fullName" name="fullName" required>
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" required>
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
