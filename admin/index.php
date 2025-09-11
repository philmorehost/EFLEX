<?php
$pageTitle = "Admin Dashboard";
// Use __DIR__ to ensure the path is correct, regardless of where the script is included from.
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">

    <!-- Sidebar -->
    <div class="bg-light border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">
            <i class="bi bi-shield-lock"></i> <?php echo SITE_NAME; ?>
        </div>
        <div class="list-group list-group-flush my-3">
            <a href="#" class="list-group-item list-group-item-action bg-transparent text-dark active">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a href="#" class="list-group-item list-group-item-action bg-transparent text-dark fw-bold">
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
            <a href="../index.php" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold">
                <i class="bi bi-box-arrow-left me-2"></i> Logout
            </a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Dashboard</h2>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle second-text fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i>Super Admin
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><a class="dropdown-item" href="#">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="container-fluid px-4">
            <h3 class="fs-4 mb-3">Welcome, Admin!</h3>
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
