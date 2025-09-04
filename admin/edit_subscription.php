<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";
$subscription_id = $_GET['id'] ?? 0;
$expires_at = '';
$username = '';
$product_name = '';

if(!$subscription_id){
    header("Location: manage_subscriptions.php");
    exit;
}

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $new_expires_at = $_POST['expires_at'];
    if (empty($new_expires_at)) {
        $new_expires_at = null;
    }

    $sql_update = "UPDATE user_subscriptions SET expires_at = ? WHERE id = ?";
    if($stmt_update = $mysqli->prepare($sql_update)){
        $stmt_update->bind_param("si", $new_expires_at, $subscription_id);
        if($stmt_update->execute()){
            $_SESSION['sub_update_message'] = "Subscription expiry date updated successfully.";
            header("Location: manage_subscriptions.php");
            exit;
        } else {
            $message = '<div class="alert alert-danger">Error updating subscription.</div>';
        }
        $stmt_update->close();
    }
}


// Fetch current subscription details
$sql_fetch = "SELECT us.expires_at, u.username, p.name as product_name
              FROM user_subscriptions us
              JOIN users u ON us.user_id = u.id
              JOIN products p ON us.product_id = p.id
              WHERE us.id = ?";
if($stmt_fetch = $mysqli->prepare($sql_fetch)){
    $stmt_fetch->bind_param("i", $subscription_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if($sub = $result->fetch_assoc()){
        $expires_at = $sub['expires_at'];
        $username = $sub['username'];
        $product_name = $sub['product_name'];
    } else {
        // Subscription not found
        header("Location: manage_subscriptions.php");
        exit;
    }
    $stmt_fetch->close();
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Edit Subscription</h1>
    <a href="manage_subscriptions.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Subscriptions</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit"></i> Edit Subscription #<?php echo $subscription_id; ?>
    </div>
    <div class="card-body">
        <form action="edit_subscription.php?id=<?php echo $subscription_id; ?>" method="post">
            <div class="mb-3">
                <label class="form-label">User</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" readonly>
            </div>
             <div class="mb-3">
                <label class="form-label">Product</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product_name); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="expires_at" class="form-label">Expiry Date</label>
                <input type="date" name="expires_at" id="expires_at" class="form-control" value="<?php echo $expires_at ? date('Y-m-d', strtotime($expires_at)) : ''; ?>">
                <div class="form-text">Leave blank for a non-expiring subscription.</div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                 <button type="submit" class="btn btn-primary">Update Subscription</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
