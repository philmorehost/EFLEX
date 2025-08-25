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
$message = "";

// Handle Payment Proof Upload
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_proof'])){
    $order_id = $_POST['order_id'];

    if(isset($_FILES["payment_proof"]) && $_FILES["payment_proof"]["error"] == 0){
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "pdf" => "application/pdf"];
        $filename = $_FILES["payment_proof"]["name"];
        $filetype = $_FILES["payment_proof"]["type"];
        $filesize = $_FILES["payment_proof"]["size"];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) $message = '<div class="alert alert-danger">Error: Please select a valid file format (JPG, PNG, PDF).</div>';

        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) $message = '<div class="alert alert-danger">Error: File size is larger than 5MB.</div>';

        if(in_array($filetype, $allowed) && empty($message)){
            $new_filename = "proof_" . $order_id . "_" . uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES["payment_proof"]["tmp_name"], "uploads/payment_proofs/" . $new_filename)){
                // File uploaded successfully, now update the order
                $sql_update = "UPDATE orders SET payment_proof = ?, status = 'Processing' WHERE id = ? AND user_id = ?";
                if($stmt_update = $mysqli->prepare($sql_update)){
                    $stmt_update->bind_param("sii", $new_filename, $order_id, $user_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                    $message = '<div class="alert alert-success">Payment proof uploaded successfully. Your order is now being processed.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Error: There was a problem uploading your file.</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-danger">Error: No file was uploaded or there was an upload error.</div>';
    }
}


// Pagination and fetching orders logic...
// ... (same as before) ...
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 5;
$offset = ($page - 1) * $records_per_page;
$sql_total = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
if($stmt_total = $mysqli->prepare($sql_total)){
    $stmt_total->bind_param("i", $user_id);
    $stmt_total->execute();
    $total_records = $stmt_total->get_result()->fetch_row()[0];
    $stmt_total->close();
}
$total_pages = ceil($total_records / $records_per_page);
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
    <h2>My Orders</h2>
    <?php echo $message; ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php if(count($orders) > 0): ?>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo $order['created_at']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($order['status']); ?></span></td>
                        <td>
                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View Details</a>
                            <?php if($order['status'] == 'Awaiting Payment'): ?>
                                <!-- Button to trigger modal -->
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal<?php echo $order['id']; ?>">
                                  Upload Proof
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="uploadModal<?php echo $order['id']; ?>" tabindex="-1">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title">Upload Payment Proof for Order #<?php echo $order['id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                      </div>
                                      <div class="modal-body">
                                        <form action="my_orders.php" method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <div class="mb-3">
                                                <label for="payment_proof" class="form-label">Select file (JPG, PNG, PDF)</label>
                                                <input class="form-control" type="file" name="payment_proof" required>
                                            </div>
                                            <button type="submit" name="upload_proof" class="btn btn-primary">Upload</button>
                                        </form>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">You have not placed any orders yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center mt-4">
        <!-- ... pagination links ... -->
      </ul>
    </nav>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
