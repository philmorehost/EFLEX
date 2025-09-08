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

$user_id = $_SESSION['id'];

// Fetch orders for the current user
$sql = "SELECT id, created_at, total_amount, status FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders = [];
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Function to get badge color based on status
function get_status_badge_user($status) {
    switch (strtolower($status)) {
        case 'awaiting payment':
        case 'pending':
            return 'bg-warning text-dark';
        case 'processing':
            return 'bg-info text-dark';
        case 'shipped':
            return 'bg-primary';
        case 'delivered':
        case 'completed':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <?php include 'includes/account_nav.php'; ?>
        </div>
        <div class="col-md-9">
            <h3>My Orders</h3>
            <p>View the history of your recent orders.</p>
            <hr>
            <div class="card">
                <div class="card-header">
                    Order History
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['order_success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $_SESSION['order_success']; unset($_SESSION['order_success']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($orders) > 0): ?>
                                    <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date("M j, Y", strtotime($order['created_at'])); ?></td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td><span class="badge <?php echo get_status_badge_user($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                                        <td class="text-end">
                                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">You have not placed any orders yet. <a href="products.php">Browse Classes</a></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
