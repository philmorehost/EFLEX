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
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): // Super Admin only ?>
        <a href="<?php echo BASE_URL; ?>admin/roles.php" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-person-rolodex me-2"></i> Role Management
        </a>
        <?php endif; ?>
        <a href="#testSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-card-checklist me-2"></i> Test Management <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="testSubmenu">
            <a href="<?php echo BASE_URL; ?>admin/question-categories.php" class="list-group-item list-group-item-action bg-transparent text-dark ps-5">Categories</a>
            <a href="<?php echo BASE_URL; ?>admin/questions.php" class="list-group-item list-group-item-action bg-transparent text-dark ps-5">Question Bank</a>
            <a href="<?php echo BASE_URL; ?>admin/tests.php" class="list-group-item list-group-item-action bg-transparent text-dark ps-5">Manage Tests</a>
        </div>
        <a href="#resultsSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-bar-chart-line me-2"></i> Results & Analytics <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="resultsSubmenu">
            <a href="<?php echo BASE_URL; ?>admin/results.php" class="list-group-item list-group-item-action bg-transparent text-dark ps-5">Test Results</a>
            <a href="<?php echo BASE_URL; ?>admin/live-monitoring.php" class="list-group-item list-group-item-action bg-transparent text-dark ps-5">Live Monitoring</a>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/bulk-email.php" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-envelope me-2"></i> Bulk Email
        </a>
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): // Super Admin only ?>
        <a href="<?php echo BASE_URL; ?>admin/settings.php" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
            <i class="bi bi-gear me-2"></i> Settings
        </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>logout.php" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold">
            <i class="bi bi-box-arrow-left me-2"></i> Logout
        </a>
    </div>
</div>
<!-- /#sidebar-wrapper -->
