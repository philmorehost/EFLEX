<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(get_setting('site_title') ?: 'Codester Clone'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(get_setting('site_description')); ?>">

    <!-- PWA -->
    <link rel="manifest" href="manifest.json.php">
    <meta name="theme-color" content="<?php echo htmlspecialchars(get_setting('pwa_theme_color') ?: '#ffffff'); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="index.php">CodesterClone</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <?php if (is_logged_in()): ?>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Logout</a>
            </li>
            <?php if (is_admin()): ?>
              <li class="nav-item">
                <a class="nav-link" href="admin/index.php">Admin</a>
              </li>
            <?php endif; ?>
        <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="register.php">Register</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_SESSION['admin_id'])): ?>
    <div class="alert alert-warning">
        You are currently logged in as a user.
        <a href="return_admin.php" class="btn btn-sm btn-warning">Return to Admin Panel</a>
    </div>
    <?php endif; ?>