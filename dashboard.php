<?php
require_once __DIR__ . '/templates/header.php';
protect_page(); // This function ensures only logged-in users can see this page

$user = get_current_user();
?>

<h1>Dashboard</h1>
<p class="lead">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Your Details
            </div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
            <a href="submit-product.php" class="list-group-item list-group-item-action">Submit New Product</a>
            <a href="my-products.php" class="list-group-item list-group-item-action">My Products</a>
            <a href="edit-profile.php" class="list-group-item list-group-item-action">Edit Profile</a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>