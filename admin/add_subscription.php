<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";

// Fetch users for dropdown
$users_result = $mysqli->query("SELECT id, username FROM users WHERE role = 'customer' ORDER BY username ASC");
$users = $users_result->fetch_all(MYSQLI_ASSOC);

// Fetch all products for dropdown
$products_result = $mysqli->query("SELECT id, name FROM products ORDER BY name ASC");
$products = $products_result->fetch_all(MYSQLI_ASSOC);


// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subscription'])){
    $user_id = $_POST['user_id'];
    $product_id = $_POST['product_id'];
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    if(empty($user_id) || empty($product_id)){
        $message = '<div class="alert alert-danger">Please select a user and a product.</div>';
    } else {
        // For simplicity, we assume order_id 0 for manually added subscriptions.
        $order_id = 0;
        $sql = "INSERT INTO user_subscriptions (user_id, product_id, order_id, status, expires_at) VALUES (?, ?, ?, 'active', ?)";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("iiis", $user_id, $product_id, $order_id, $expires_at);
            if($stmt->execute()){
                $message = '<div class="alert alert-success">Subscription added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error adding subscription. The user might already be subscribed to this product.</div>';
            }
            $stmt->close();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Add New Subscription</h1>
    <a href="manage_subscriptions.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Subscriptions</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header"><i class="fas fa-plus-circle"></i> Create a New Subscription</div>
    <div class="card-body">
        <p>Manually grant a user access to a subscription product.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="user_id" class="form-label">Select User</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">Choose a user...</option>
                    <?php foreach($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="product_id" class="form-label">Select Subscription Product</label>
                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="">Choose a product...</option>
                    <?php foreach($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="expires_at" class="form-label">Expiry Date</label>
                <input type="date" name="expires_at" id="expires_at" class="form-control">
                <div class="form-text">Leave blank for a non-expiring subscription.</div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                <button type="submit" name="add_subscription" class="btn btn-primary">Add Subscription</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
