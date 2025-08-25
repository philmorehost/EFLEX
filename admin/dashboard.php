<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin. If not, redirect them to the homepage.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. You are logged in as an admin.</p>
    <p>Here you can manage products, orders, and users.</p>

    <div class="list-group">
      <a href="manage_products.php" class="list-group-item list-group-item-action">Manage Products</a>
      <a href="manage_categories.php" class="list-group-item list-group-item-action">Manage Categories</a>
      <a href="#" class="list-group-item list-group-item-action">Manage Orders</a>
      <a href="#" class="list-group-item list-group-item-action">Manage Users</a>
    </div>

</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
