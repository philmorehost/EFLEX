<?php
// This should be included at the top of every page
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . SITE_NAME : SITE_NAME; ?></title>

    <meta name="description" content="A robust and scalable platform for conducting computer-based tests.">

    <!-- PWA & Mobile Meta -->
    <link rel="manifest" href="<?php echo BASE_URL; ?>manifest.php">
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo SITE_NAME; ?>">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>assets/img/icons/icon-152x152.png">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/favicon.ico" type="image/x-icon">

    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <?php
    // Conditionally load landing page CSS
    if (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_SESSION['user_id'])) {
        echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/landing.css">';
    }
    ?>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- PWA Service Worker Registration -->
    <script src="<?php echo BASE_URL; ?>assets/js/register-sw.js"></script>
</head>
<body>

<?php if (isset($_SESSION['user_id']) && !in_array($_SESSION['role_id'], [1, 2, 3])): ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php"><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar" aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['first_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>knowledge_base.php">Knowledge Base</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="wrapper">
    <!-- Main content will go here -->
