<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";
if(isset($_SESSION['order_update_message'])){
    $message = '<div class="alert alert-success">'.$_SESSION['order_update_message'].'</div>';
    unset($_SESSION['order_update_message']);
}


// Search and Pagination variables
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

// Base SQL and parameters
$sql_from_join = "FROM orders o JOIN users u ON o.user_id = u.id";
$sql_where = "";
$params = [];
$param_types = "";

if(!empty($search_query)){
    // Search by Order ID (numeric check) or Username
    if(is_numeric($search_query)){
        $sql_where = " WHERE o.id = ?";
        $params[] = &$search_query;
        $param_types .= "i";
    } else {
        $sql_where = " WHERE u.username LIKE ?";
        $search_term = "%" . $search_query . "%";
        $params[] = &$search_term;
        $param_types .= "s";
    }
}

// Get total number of orders
$total_records_sql = "SELECT COUNT(*) " . $sql_from_join . $sql_where;
if($stmt_total = $mysqli->prepare($total_records_sql)){
    if(!empty($search_query)){
        $stmt_total->bind_param($param_types, ...$params);
    }
    $stmt_total->execute();
    $total_records_result = $stmt_total->get_result();
    $total_records = $total_records_result->fetch_row()[0];
    $stmt_total->close();
} else {
    $total_records = 0;
}
$total_pages = ceil($total_records / $records_per_page);

// Fetch orders for the current page
$sql = "SELECT o.id, u.username, o.total_amount, o.status, o.created_at
        " . $sql_from_join . $sql_where . "
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = &$records_per_page;
$params[] = &$offset;
$param_types .= "ii";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param($param_types, ...$params);
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
        case 'processing':
            return 'badge bg-info text-dark';
        case 'shipped':
            return 'badge bg-primary';
        case 'delivered':
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

<!-- Search Form -->
<div class="card mb-3">
    <div class="card-body">
        <form action="manage_orders.php" method="get" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search by Order ID or Customer..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <?php if(!empty($search_query)): ?>
                <a href="manage_orders.php" class="btn btn-secondary ms-2"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

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
                            <td><span class="<?php echo get_status_badge($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                            <td class="text-end">
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No orders found.</td></tr>
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
                    <li class="page-item"><a class="page-link" href="manage_orders.php?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search_query); ?>">Previous</a></li>
                <?php endif; ?>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="manage_orders.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="manage_orders.php?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search_query); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
