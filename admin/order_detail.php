<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

// Check if Order ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: manage_orders.php");
    exit;
}
$order_id = $_GET['id'];

// Handle status update
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])){
    $new_status = $_POST['status'];
    if(!empty($new_status)){
        $sql_update = "UPDATE orders SET status = ? WHERE id = ?";
        if($stmt_update = $mysqli->prepare($sql_update)){
            $stmt_update->bind_param("si", $new_status, $order_id);
            if($stmt_update->execute()){
                $_SESSION['order_update_message'] = "Order #$order_id status has been updated.";
            }
            $stmt_update->close();
        }
    }
    header("location: order_detail.php?id=" . $order_id);
    exit;
}

// Fetch Order and Customer Details
$sql_order = "SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?";
$order = null;
if($stmt_order = $mysqli->prepare($sql_order)){
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
    $result_order = $stmt_order->get_result();
    if($result_order->num_rows == 1){
        $order = $result_order->fetch_assoc();
    } else {
        echo "Order not found."; exit;
    }
    $stmt_order->close();
}

// Fetch Order Items
$sql_items = "SELECT oi.*, p.name as product_name, p.image as product_image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$order_items = [];
if($stmt_items = $mysqli->prepare($sql_items)){
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $order_items = $result_items->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Order Details</h1>
    <a href="manage_orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-box-open"></i> Order #<?php echo $order['id']; ?> Items</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../uploads/<?php echo htmlspecialchars($item['product_image']); ?>" class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </div>
                                </td>
                                <td>x <?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-end">$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-receipt"></i> Order Summary</div>
            <div class="card-body">
                <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($order['status']); ?></span></p>
                <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></p>
                <p><strong>Total:</strong> <span class="fw-bold fs-5">$<?php echo number_format($order['total_amount'], 2); ?></span></p>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-user"></i> Customer Details</div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
        </div>
         <div class="card">
            <div class="card-header"><i class="fas fa-edit"></i> Update Order Status</div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $order_id); ?>" method="post">
                    <select name="status" class="form-select">
                        <option value="Pending" <?php if($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Processing" <?php if($order['status'] == 'Processing') echo 'selected'; ?>>Processing</option>
                        <option value="Shipped" <?php if($order['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                        <option value="Delivered" <?php if($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                        <option value="Cancelled" <?php if($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary mt-2 w-100">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
