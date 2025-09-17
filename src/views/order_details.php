<?php
require_once __DIR__ . '/partials/header.php';
// The controller provides $order
?>

<div class="page-header">
    <h1>Order Details</h1>
</div>

<div class="container">
    <h3>Order #<?php echo $order['id']; ?></h3>
    <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
    <p><strong>Total:</strong> $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
    <p><strong>Status:</strong> <span class="status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>

    <hr>

    <h3>Purchased Items</h3>
    <table class="product-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>License Key</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>
                        <?php
                            // In a real app, you'd join with the licenses table here.
                            // For now, we'll just show a placeholder.
                            echo 'LICENSE-KEY-PLACEHOLDER';
                        ?>
                    </td>
                    <td>
                        <a href="/download?item_id=<?php echo $item['id']; ?>" class="btn btn-sm btn-success">Download</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
