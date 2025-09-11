<?php
$pageTitle = "Admin Dashboard";
// Use __DIR__ to ensure the path is correct
require_once __DIR__ . '/../includes/config.php';

// --- Authentication Check ---
// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    // If not, redirect to the login page.
    header("Location: ../login.php?error=authrequired");
    exit;
}

// --- Role Check ---
// Check if the user has an allowed role (Super Admin, Admin, or Staff).
// Role IDs: 1 = Super Admin, 2 = Admin, 3 = Staff
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    // If the user's role is not allowed, destroy the session and redirect.
    session_destroy();
    header("Location: ../login.php?error=accessdenied");
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Dashboard</h2>
            </div>

            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <h3 class="fs-4 mb-3">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h3>
            <p>This is the main dashboard content area. Fintech-style cards and charts will go here.</p>

            <div class="row g-3 my-2">
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2">720</h3>
                            <p class="fs-5">Students</p>
                        </div>
                        <i class="bi bi-people fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                    </div>
                </div>
                 <!-- Add more cards here -->
            </div>

        </div>
    </div>
</div>
<!-- /#page-content-wrapper -->

<script>
    // JS for sidebar toggle
    document.getElementById("menu-toggle").addEventListener("click", function() {
        document.getElementById("admin-wrapper").classList.toggle("toggled");
    });
</script>

<?php
// Use __DIR__ to ensure the path is correct
require_once __DIR__ . '/../includes/footer.php';
?>
