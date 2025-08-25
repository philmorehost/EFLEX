<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Include database connection file
require_once "../includes/db_connect.php";

// Check if Order ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: manage_orders.php");
    exit;
}
$order_id = $_GET['id'];

// Handle status update
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])){
    $new_status = $_POST['status'];
    $sql_update = "UPDATE orders SET status = ? WHERE id = ?";
    if($stmt_update = $mysqli->prepare($sql_update)){
        $stmt_update->bind_param("si", $new_status, $order_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    // It's good practice to redirect to refresh the page and prevent resubmission
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
    }
    $stmt_order->close();
}

if(!$order){
    echo "Order not found.";
    exit;
}

// Fetch Order Items
$sql_items = "SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$order_items = [];
if($stmt_items = $mysqli->prepare($sql_items)){
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $order_items = $result_items->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Navbar -->
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="collapse navbar-collapse">
             <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_categories.php">Categories</a></li>
                <li class="nav-item"><a class="nav-link active" href="manage_orders.php">Orders</a></li>
            </ul>
            <ul class="navbar-nav ms-auto"><li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li></ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Order Details for #<?php echo $order['id']; ?></h2>
    <a href="manage_orders.php" class="btn btn-secondary mb-3">Back to Orders</a>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Order Items</div>
                <div class="card-body">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            <?php foreach($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Order Summary</div>
                <div class="card-body">
                    <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                    <p><strong>Date:</strong> <?php echo $order['created_at']; ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($order['status']); ?></span></p>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Customer Details</div>
                <div class="card-body">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                </div>
            </div>
             <div class="card">
                <div class="card-header">Update Status</div>
                <div class="card-body">
                    <form action="order_detail.php?id=<?php echo $order_id; ?>" method="post">
                        <select name="status" class="form-select">
                            <option value="Pending" <?php if($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Processing" <?php if($order['status'] == 'Processing') echo 'selected'; ?>>Processing</option>
                            <option value="Shipped" <?php if($order['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                            <option value="Completed" <?php if($order['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                            <option value="Cancelled" <?php if($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary mt-2 w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
