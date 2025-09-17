<?php require_once __DIR__ . '/../../lib/Session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeCanyon Marketplace</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="/" class="navbar-brand">Marketplace</a>
                <ul class="navbar-nav">
                    <li><a href="/">Home</a></li>
                    <li><a href="/products">Products</a></li>

                    <?php if (Session::has('user_id')): ?>
                        <?php
                            $cart = Session::get('cart', []);
                            $cart_count = count($cart);
                        ?>
                        <li><a href="/cart">Cart (<?php echo $cart_count; ?>)</a></li>
                        <li><a href="/dashboard">Dashboard</a></li>
                        <li><a href="/logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
