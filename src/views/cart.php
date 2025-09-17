<?php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/partials/header.php';

$cart = Session::get('cart', []);
$total = 0;
?>

<div class="page-header">
    <h1>Your Shopping Cart</h1>
</div>

<?php if ($success_message = Session::flash('success_message')): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="cart-container">
    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
        <a href="/products" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                        <td>
                            <form action="/cart/remove" method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php $total += $item['price']; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <h3>Total: $<?php echo htmlspecialchars(number_format($total, 2)); ?></h3>
            <form action="/orders/create" method="POST">
                <button type="submit" class="btn btn-success">Proceed to Checkout</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
