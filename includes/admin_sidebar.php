<!-- Sidebar -->
<div class="bg-light border-right" id="sidebar-wrapper">
    <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">
        <a href="<?php echo BASE_URL; ?>admin/index.php" class="text-decoration-none text-primary">
            <i class="bi bi-shield-lock"></i> <?php echo SITE_NAME; ?>
        </a>
    </div>
    <div class="list-group list-group-flush my-3">
        <a href="<?php echo BASE_URL; ?>admin/index.php" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>admin/users.php" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-people me-2"></i> User Management
        </a>
        <a href="#" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-card-checklist me-2"></i> Test Management
        </a>
        <a href="#" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-bar-chart-line me-2"></i> Results & Analytics
        </a>
        <a href="#" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-gear me-2"></i> Settings
        </a>
        <a href="<?php echo BASE_URL; ?>logout.php" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold">
            <i class="bi bi-box-arrow-left me-2"></i> Logout
        </a>
    </div>
</div>
<!-- /#sidebar-wrapper -->
