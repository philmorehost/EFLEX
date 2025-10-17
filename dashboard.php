<?php
require_once 'templates/header.php';

// Use the protect_page() function to ensure only logged-in users can access this page.
protect_page();

// Get the current user's data to display on the page.
$user = get_current_user();

// If for some reason the user data is empty, redirect to logout to clear the session.
if (empty($user)) {
    redirect('logout.php');
}
?>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action active" aria-current="true">
                Dashboard
            </a>
            <a href="submit-product.php" class="list-group-item list-group-item-action">Submit New Product</a>
            <a href="my-products.php" class="list-group-item list-group-item-action">My Products</a>
            <a href="edit-profile.php" class="list-group-item list-group-item-action">Edit Profile</a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
        </div>
    </div>
    <div class="col-md-9">
        <h1>Dashboard</h1>
        <p class="lead">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
        <hr>
        <div class="card">
            <div class="card-header">
                Your Account Details
            </div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>