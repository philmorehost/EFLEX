<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";

// Handle manual order approval
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_order'])){
    $order_id_to_approve = $_POST['order_id'];
    $user_id_to_approve = $_POST['user_id'];

    if(!empty($order_id_to_approve) && !empty($user_id_to_approve)) {
        // Use the centralized function to finalize the order
        $result = finalize_successful_order($order_id_to_approve, $user_id_to_approve);
        if($result['status'] === 'success') {
            $_SESSION['order_update_message'] = "Order #$order_id_to_approve has been approved and subscription activated.";
        } else {
            $_SESSION['order_update_message'] = "Error approving order #$order_id_to_approve: " . $result['message'];
        }
    } else {
        $_SESSION['order_update_message'] = "Could not approve order due to missing information.";
    }
    // Redirect to the same page to prevent form resubmission
    header("Location: manage_orders.php?page=" . ($_GET['page'] ?? 1));
    exit;
}


if(isset($_SESSION['order_update_message'])){
    $message = '<div class="alert alert-success">'.$_SESSION['order_update_message'].'</div>';
    unset($_SESSION['order_update_message']);
}


// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

// Get total number of orders
$total_records_result = $mysqli->query("SELECT COUNT(*) FROM orders");
$total_records = $total_records_result->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

// Fetch orders for the current page
$sql = "SELECT o.id, o.user_id, u.username, o.total_amount, o.status, o.created_at, o.payment_method
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("ii", $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $orders = [];
    $message .= '<div class="alert alert-danger">Error fetching orders.</div>';
}

// Function to get badge color based on status
function get_status_badge($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'badge bg-warning text-dark';
        case 'completed':
            return 'badge bg-success';
        case 'cancelled':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Manage Orders</h1>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-receipt"></i> All Customer Orders
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date("M j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                            <td><?php echo format_price($order['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method'] ?? 'N/A'))); ?></td>
                            <td><span class="<?php echo get_status_badge($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                            <td class="text-end">
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>
                                <?php if(strtolower($order['status']) == 'pending'): ?>
                                    <form action="manage_orders.php?page=<?php echo $page; ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $order['user_id']; ?>">
                                        <button type="submit" name="approve_order" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this order and activate the subscription?');">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
                <?php if($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="manage_orders.php?page=<?php echo $page-1; ?>">Previous</a></li>
                <?php endif; ?>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="manage_orders.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="manage_orders.php?page=<?php echo $page+1; ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
