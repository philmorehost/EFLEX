<?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Define the navigation items
$nav_items = [
    'account.php' => ['icon' => 'fas fa-user-circle', 'text' => 'My Account'],
    'my_orders.php' => ['icon' => 'fas fa-box', 'text' => 'My Orders'],
    'change_password.php' => ['icon' => 'fas fa-key', 'text' => 'Change Password']
];
?>
<div class="card">
    <div class="card-header">
       Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!
    </div>
    <div class="list-group list-group-flush">
        <?php foreach($nav_items as $url => $item): ?>
            <a href="<?php echo $url; ?>" class="list-group-item list-group-item-action <?php echo ($current_page == $url) ? 'active' : ''; ?>">
                <i class="<?php echo $item['icon']; ?> fa-fw me-2"></i><?php echo $item['text']; ?>
            </a>
        <?php endforeach; ?>
        <a href="logout.php" class="list-group-item list-group-item-action">
            <i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout
        </a>
    </div>
</div>
