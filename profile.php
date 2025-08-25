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

// Fetch user's orders
$user_id = $_SESSION['id'];
$sql = "SELECT id, created_at, total_amount, status FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders = [];
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<div class="container mt-5">
    <h2>My Profile</h2>
    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</p>
    <p>From this dashboard, you can view your recent orders.</p>

    <hr>

    <h4>Order History</h4>
    <?php if(count($orders) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo $order['created_at']; ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($order['status']); ?></span></td>
                    <td><a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View Details</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p>You have not placed any orders yet.</p>
    <?php endif; ?>

</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
