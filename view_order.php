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

// Include the header and database connection
include 'includes/header.php';
require_once 'includes/db_connect.php';

// Check if Order ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: my_orders.php");
    exit;
}
$order_id = $_GET['id'];
$user_id = $_SESSION['id'];

// Fetch Order Details and verify ownership
$sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order = null;
if($stmt_order = $mysqli->prepare($sql_order)){
    $stmt_order->bind_param("ii", $order_id, $user_id);
    $stmt_order->execute();
    $result_order = $stmt_order->get_result();
    if($result_order->num_rows == 1){
        $order = $result_order->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Order not found or you do not have permission to view it.";
        header("location: my_orders.php");
        exit;
    }
    $stmt_order->close();
}

// Fetch Order Items
$sql_items = "SELECT oi.*, p.name as class_name, p.image as class_image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$order_items = [];
if($stmt_items = $mysqli->prepare($sql_items)){
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $order_items = $result_items->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <?php include 'includes/account_nav.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Order Details</h3>
                <a href="my_orders.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            </div>
            <hr>
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <span>Order #<?php echo $order['id']; ?></span>
                    <span>Placed on: <?php echo date("F j, Y", strtotime($order['created_at'])); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/<?php echo htmlspecialchars($item['class_image']); ?>" class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;" alt="<?php echo htmlspecialchars($item['class_name']); ?>">
                                            <?php echo htmlspecialchars($item['class_name']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end"><?php echo format_price($item['price']); ?></td>
                                    <td class="text-end"><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                             <p class="text-end"><strong>Subtotal:</strong> <?php echo format_price($order['total_amount']); ?></p>
                             <p class="text-end"><strong>Shipping:</strong> <?php echo format_price(0); ?></p>
                             <h4 class="text-end"><strong>Total:</strong> <?php echo format_price($order['total_amount']); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <strong>Shipping Address:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
