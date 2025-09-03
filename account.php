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

require_once 'includes/db_connect.php';

// Fetch user details
$user_id = $_SESSION['id'];
$sql = "SELECT username, email, created_at FROM users WHERE id = ?";
$user = null;
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Include the header
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <?php include 'includes/account_nav.php'; ?>
        </div>
        <div class="col-md-9">
            <h3>My Account</h3>
            <p>From your account dashboard you can view your recent orders, manage your shipping addresses and edit your password and account details.</p>
            <hr>
            <div class="card">
                <div class="card-header">
                    Account Information
                </div>
                <div class="card-body">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email Address:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Account Registered:</strong> <?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
