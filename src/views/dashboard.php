<?php
// The controller now handles the session check and data fetching.
// The following variables are made available by the controller:
// $user_type, $username, $products

require_once __DIR__ . '/partials/header.php';
?>

<div class="page-header">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>This is your dashboard. From here you can manage your profile and items.</p>
</div>

<?php if ($success_message = Session::flash('success_message')): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message = Session::flash('error_message')): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="dashboard-content">
    <?php if ($user_type === 'seller'): ?>
        <h2>Your Products</h2>
        <a href="/products/create" class="btn btn-primary" style="margin-bottom: 20px;">Upload New Product</a>
        <?php if (empty($products)): ?>
            <p>You have not uploaded any products yet.</p>
        <?php else: ?>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Date Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>$<?php echo htmlspecialchars($product['price']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                            <td>
                                <!-- Actions will be added in a future step -->
                                N/A
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php else: // Buyer's dashboard ?>
        <h2>Your Orders</h2>
        <p>You have not made any purchases yet.</p>
        <a href="/products" class="btn btn-primary">Browse Products</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
