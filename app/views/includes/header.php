<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_NAME') ? SITE_NAME : 'VTU Website'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="<?php echo BASE_URL; ?>">Home</a>
            <a href="<?php echo BASE_URL; ?>/pages/about">About</a>
            <?php if (isLoggedIn()) : ?>
                <a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a>
                <?php if ($_SESSION['user_role'] === 'admin') : ?>
                    <a href="<?php echo BASE_URL; ?>/admin/settings">Admin Settings</a>
                    <a href="<?php echo BASE_URL; ?>/admin/loans">Manage Loans</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/users/logout">Logout</a>
            <?php else : ?>
                <a href="<?php echo BASE_URL; ?>/users/register">Register</a>
                <a href="<?php echo BASE_URL; ?>/users/login">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>