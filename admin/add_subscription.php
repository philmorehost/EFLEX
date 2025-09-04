<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';
require_once '../includes/google_drive_api.php'; // Include the Google Drive API functions

$message = "";

// Fetch users for dropdown
$users_result = $mysqli->query("SELECT id, username, email FROM users WHERE role = 'customer' ORDER BY username ASC");
$users = $users_result->fetch_all(MYSQLI_ASSOC);

// Fetch subscription products for dropdown
// Only show products that have at least one Google Drive file linked
$products_result = $mysqli->query("SELECT p.id, p.name FROM products p JOIN product_google_drive_files pgdf ON p.id = pgdf.product_id GROUP BY p.id ORDER BY p.name ASC");
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
                // --- Grant Google Drive Permissions ---
                // 1. Get user's email
                $user_email = '';
                $sql_user = "SELECT email FROM users WHERE id = ?";
                if($stmt_user = $mysqli->prepare($sql_user)){
                    $stmt_user->bind_param("i", $user_id);
                    $stmt_user->execute();
                    $stmt_user->bind_result($user_email);
                    $stmt_user->fetch();
                    $stmt_user->close();
                }

                // 2. Get file IDs for the product
                $file_ids = [];
                $sql_files = "SELECT google_drive_file_id FROM product_google_drive_files WHERE product_id = ?";
                if($stmt_files = $mysqli->prepare($sql_files)){
                    $stmt_files->bind_param("i", $product_id);
                    $stmt_files->execute();
                    $result_files = $stmt_files->get_result();
                    while($row = $result_files->fetch_assoc()){
                        $file_ids[] = $row['google_drive_file_id'];
                    }
                    $stmt_files->close();
                }

                // 3. Grant permission for each file
                if(!empty($user_email) && !empty($file_ids)){
                    $permissions_granted = true;
                    $permission_errors = [];
                    foreach($file_ids as $file_id){
                        $result = grant_file_permission($file_id, $user_email);
                        if(isset($result['error'])){
                            $permissions_granted = false;
                            $permission_errors[] = "File ID $file_id: " . $result['error']['message'];
                        }
                    }
                    if($permissions_granted){
                        $message = '<div class="alert alert-success">Subscription added successfully and Google Drive access granted!</div>';
                    } else {
                        $message = '<div class="alert alert-warning">Subscription added, but failed to grant Google Drive access for some files. Please check manually. Errors: ' . implode(", ", $permission_errors) . '</div>';
                    }
                } else {
                     $message = '<div class="alert alert-success">Subscription added successfully. No Google Drive files were associated with this product.</div>';
                }

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
        <p>Manually grant a user access to a subscription product. This is useful for activating subscriptions after a manual payment like a bank transfer.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="user_id" class="form-label">Select User</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">Choose a user...</option>
                    <?php foreach($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</option>
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
                <div class="form-text">Only products that have Google Drive files linked to them are shown here.</div>
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
