<?php
// Include the admin header
include 'includes/header.php';

// --- Dashboard Specific Content ---

// Example: Fetch some stats for the dashboard
// For demonstration, let's count total classes, users, and orders.
require_once '../includes/db_connect.php'; // Ensure database connection

// Count Classes
$product_count_sql = "SELECT COUNT(*) as total FROM products";
$product_count_result = $mysqli->query($product_count_sql);
$product_count = $product_count_result->fetch_assoc()['total'];

// Count Users
$user_count_sql = "SELECT COUNT(*) as total FROM users";
$user_count_result = $mysqli->query($user_count_sql);
$user_count = $user_count_result->fetch_assoc()['total'];

// Count Orders
$order_count_sql = "SELECT COUNT(*) as total FROM orders";
$order_count_result = $mysqli->query($order_count_sql);
$order_count = $order_count_result->fetch_assoc()['total'];

?>

<h1>Admin Dashboard</h1>
<p class="lead">Welcome back, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Here's a snapshot of your store.</p>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Total Classes</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $product_count; ?></h5>
                <a href="manage_products.php" class="text-white">View Classes &rarr;</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Total Users</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $user_count; ?></h5>
                <a href="manage_users.php" class="text-white">View Users &rarr;</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Total Orders</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $order_count; ?></h5>
                <a href="manage_orders.php" class="text-white">View Orders &rarr;</a>
            </div>
        </div>
    </div>
</div>

<!-- You can add more dashboard widgets here -->

<?php
// Include the admin footer
include 'includes/footer.php';
?>
