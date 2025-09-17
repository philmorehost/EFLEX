<?php
require_once __DIR__ . '/partials/header.php';
$gateway = htmlspecialchars($_GET['gateway'] ?? 'Unknown');
$ref = htmlspecialchars($_GET['ref'] ?? 'Unknown');
?>

<div class="page-header">
    <h1>Mock Payment Page (<?php echo $gateway; ?>)</h1>
</div>

<div class="container">
    <p>This page simulates the payment gateway's hosted page.</p>
    <p>In a real application, the user would enter their payment details here. Once payment is complete, the gateway would redirect them back to our site and also send a webhook to our server.</p>
    <p>Your payment reference is: <strong><?php echo $ref; ?></strong></p>

    <hr>

    <h3>Developer Simulation</h3>
    <p>To simulate the final step, click the link below. This simulates the webhook call that the payment gateway would make to our server to confirm the payment.</p>

    <a href="/webhook/payment?ref=<?php echo $ref; ?>&status=success" class="btn btn-primary">
        Simulate Successful Payment Webhook
    </a>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
