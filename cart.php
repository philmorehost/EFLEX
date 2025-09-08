<?php
// We need to start the session on all pages to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'includes/db_connect.php';

// Initialize the cart session if it doesn't exist
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = array();
}

// Handle "Subscribe Now" action (add single item and go to checkout)
if(isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id']) && isset($_GET['single'])){
    $product_id = $_GET['id'];
    // Clear cart and add only this item
    $_SESSION['cart'] = [];
    $_SESSION['cart'][$product_id] = 1;
    header('location: checkout.php');
    exit();
}

// Handle Add to Cart action
if(isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])){
    $product_id = $_GET['id'];
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

    if(isset($_SESSION['cart'][$product_id])){
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header('location: cart.php');
    exit();
}

// Handle Remove from Cart action
if(isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])){
    $product_id = $_GET['id'];
    unset($_SESSION['cart'][$product_id]);
    header('location: cart.php');
    exit();
}

// Handle Update Quantity action
if(isset($_POST['action']) && $_POST['action'] == 'update'){
    $product_id = $_POST['id'];
    $quantity = (int)$_POST['quantity'];

    if($quantity > 0){
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        // Remove item if quantity is 0 or less
        unset($_SESSION['cart'][$product_id]);
    }
    header('location: cart.php');
    exit();
}


// --- Logic to display the cart ---
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

            $cart_items[] = [
                'id' => $product_id,
                'name' => $row['name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
        $stmt->close();
    }
}

// Include the header
include 'includes/header.php';
?>

<h2>Your Classes</h2>

<?php if(!empty($cart_items)): ?>
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th scope="col" colspan="2">Class</th>
                <th scope="col">Price</th>
                <th scope="col" style="width: 150px;">Quantity</th>
                <th scope="col" class="text-end">Subtotal</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cart_items as $item): ?>
            <tr>
                <td style="width: 100px;"><img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['name']); ?>"></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo format_price($item['price']); ?></td>
                <td>
                    <form action="cart.php" method="post" class="d-flex">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <input type="number" name="quantity" class="form-control form-control-sm" value="<?php echo $item['quantity']; ?>" min="1">
                        <button type="submit" class="btn btn-sm btn-primary ms-2">Update</button>
                    </form>
                </td>
                <td class="text-end"><?php echo format_price($item['subtotal']); ?></td>
                <td class="text-end"><a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger">&times;</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Total</strong></td>
                <td class="text-end"><strong><?php echo format_price($total_price); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>
<div class="d-flex justify-content-end">
    <a href="products.php" class="btn btn-secondary me-2">Browse More Classes</a>
    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
</div>
<?php else: ?>
    <div class="alert alert-info">You have no classes selected.</div>
    <a href="products.php" class="btn btn-primary">Browse Classes</a>
<?php endif; ?>

<?php
// Include the footer
include 'includes/footer.php';
?>
