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

// Get current user's ID
$user_id = $_SESSION['id'];

// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 5; // Show 5 orders per page
$offset = ($page - 1) * $records_per_page;

// Get total number of orders for the current user
$sql_total = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
if($stmt_total = $mysqli->prepare($sql_total)){
    $stmt_total->bind_param("i", $user_id);
    $stmt_total->execute();
    $total_records = $stmt_total->get_result()->fetch_row()[0];
    $stmt_total->close();
} else {
    $total_records = 0;
}
$total_pages = ceil($total_records / $records_per_page);


// Fetch user's orders for the current page
$sql = "SELECT id, created_at, total_amount, status FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$orders = [];
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("iii", $user_id, $records_per_page, $offset);
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

    <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mt-4">
        <?php if($page > 1): ?><li class="page-item"><a class="page-link" href="profile.php?page=<?php echo $page-1; ?>">Previous</a></li><?php endif; ?>
        <?php for($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="profile.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
        <?php if($page < $total_pages): ?><li class="page-item"><a class="page-link" href="profile.php?page=<?php echo $page+1; ?>">Next</a></li><?php endif; ?>
      </ul>
    </nav>

    <?php else: ?>
    <p>You have not placed any orders yet.</p>
    <?php endif; ?>

</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
