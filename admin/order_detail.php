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

// Handle status update from both dropdown and approve/reject buttons
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $new_status = "";
    if(isset($_POST['update_status'])){
        $new_status = $_POST['status'];
    } elseif(isset($_POST['approve_payment'])){
        $new_status = 'Completed';
    } elseif(isset($_POST['reject_payment'])){
        $new_status = 'Awaiting Payment';
        // Also clear the payment proof on rejection
        $mysqli->query("UPDATE orders SET payment_proof = NULL WHERE id = $order_id");
    }

    if(!empty($new_status)){
        $sql_update = "UPDATE orders SET status = ? WHERE id = ?";
        if($stmt_update = $mysqli->prepare($sql_update)){
            $stmt_update->bind_param("si", $new_status, $order_id);
            $stmt_update->execute();
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
    }
    $stmt_order->close();
}

if(!$order){ echo "Order not found."; exit; }

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
    <link href="../css/custom_style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><!-- Navbar --></nav>

<div class="container mt-4">
    <h2>Order Details for #<?php echo $order['id']; ?></h2>
    <a href="manage_orders.php" class="btn btn-secondary mb-3">Back to Orders</a>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Order Items</div>
                <div class="card-body">
                    <table class="table">
                        <!-- Table content -->
                    </table>
                </div>
            </div>

            <?php if($order['payment_proof']): ?>
            <div class="card">
                <div class="card-header">Payment Proof</div>
                <div class="card-body">
                    <a href="../uploads/payment_proofs/<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank">
                        <img src="../uploads/payment_proofs/<?php echo htmlspecialchars($order['payment_proof']); ?>" class="img-fluid" alt="Payment Proof">
                    </a>
                    <?php if($order['status'] == 'Processing'): ?>
                    <form action="order_detail.php?id=<?php echo $order_id; ?>" method="post" class="mt-3 d-flex justify-content-end">
                        <button type="submit" name="reject_payment" class="btn btn-danger me-2">Reject</button>
                        <button type="submit" name="approve_payment" class="btn btn-success">Approve</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Order Summary</div>
                <div class="card-body">
                    <!-- Summary content -->
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Customer Details</div>
                <div class="card-body">
                    <!-- Customer content -->
                </div>
            </div>
             <div class="card">
                <div class="card-header">Update Status</div>
                <div class="card-body">
                    <form action="order_detail.php?id=<?php echo $order_id; ?>" method="post">
                        <select name="status" class="form-select">
                            <option value="Awaiting Payment" <?php if($order['status'] == 'Awaiting Payment') echo 'selected'; ?>>Awaiting Payment</option>
                            <option value="Processing" <?php if($order['status'] == 'Processing') echo 'selected'; ?>>Processing</option>
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
