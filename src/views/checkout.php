<?php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/partials/header.php';

// The controller will provide the $order and $orderItems variables
?>

<div class="page-header">
    <h1>Checkout</h1>
    <p>Please review your order and proceed to payment.</p>
</div>

<div class="checkout-container">
    <h2>Order Summary (ID: <?php echo htmlspecialchars($order['id']); ?>)</h2>

    <table class="order-summary-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <td>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="payment-options">
        <h3>Select Payment Method</h3>
        <p>This is a simulation. In a real application, these would integrate with payment gateways.</p>
        <div class="payment-buttons">
            <form action="/pay/paystack" method="POST" style="display: inline-block;">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <button type="submit" class="btn btn-primary">Pay with Paystack (Mock)</button>
            </form>
            <form action="/pay/flutterwave" method="POST" style="display: inline-block;">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <button type="submit" class="btn btn-primary">Pay with Flutterwave (Mock)</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
