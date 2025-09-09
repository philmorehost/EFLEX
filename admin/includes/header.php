<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is an admin or staff. If not, redirect them to the homepage.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], ['admin', 'staff'])){
    header("location: ../index.php");
    exit;
}

// --- Role-Based Access Control (RBAC) Check ---
// This check runs on every page that includes this header.
// It ensures that staff members can only access pages they have permissions for.
if ($_SESSION['role'] === 'staff') {
    // Get the current page filename
    $current_page = basename($_SERVER['PHP_SELF']);

    // Define pages that all staff members can access by default.
    $always_allowed_pages = ['dashboard.php', 'index.php']; // index.php usually redirects to dashboard

    if (!in_array($current_page, $always_allowed_pages)) {
        // Fetch the user's role_id from the session. It should have been set on login.
        $user_role_id = $_SESSION['role_id'] ?? 0;

        // If role_id is not set, deny access as a precaution.
        if (empty($user_role_id)) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Access Denied: Your user role is not configured correctly.'];
            header('Location: dashboard.php');
            exit;
        }

        // Check the database for permission
        $sql_check_perm = "SELECT id FROM role_permissions WHERE role_id = ? AND page_name = ?";
        $stmt_check_perm = $mysqli->prepare($sql_check_perm);
        $stmt_check_perm->bind_param("is", $user_role_id, $current_page);
        $stmt_check_perm->execute();
        $stmt_check_perm->store_result();

        if ($stmt_check_perm->num_rows === 0) {
            // No permission found, redirect with an error message
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Access Denied: You do not have permission to view this page.'
            ];
            header('Location: dashboard.php');
            exit;
        }
        $stmt_check_perm->close();
    }
}

// Include the database connection and helper functions
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Base path for assets
$base_url = "../";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Eflex</title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo $base_url; ?>css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Custom Admin CSS -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        .main-content {
            flex: 1;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            z-index: 1030; /* Higher than navbar */
            transition: transform 0.3s ease-in-out;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: #495057;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease-in-out;
        }
        .admin-header {
            display: none; /* Hidden by default, shown on mobile */
            background-color: #fff;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .sidebar-toggle-btn {
            font-size: 1.5rem;
            background: none;
            border: none;
            color: #343a40;
        }
        .ck-editor__editable_inline {
            min-height: 250px;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1020; /* Below sidebar, above content */
            display: none;
        }
        .overlay.is-active {
            display: block;
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.is-open {
                transform: translateX(0);
            }
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .admin-header {
                display: flex;
                align-items: center;
            }
        }
    </style>
</head>
<body>

<div class="sidebar" id="admin-sidebar">
    <h3 class="text-center">Eflex Admin</h3>
    <hr style="background-color: #fff;">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_products.php"><i class="fas fa-chalkboard"></i> Manage Classes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_categories.php"><i class="fas fa-tags"></i> Manage Categories</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_orders.php"><i class="fas fa-receipt"></i> Manage Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_roles.php"><i class="fas fa-user-shield"></i> Manage Roles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_subscriptions.php"><i class="fas fa-id-card"></i> Manage Subscriptions</a>
        </li>
         <li class="nav-item">
            <a class="nav-link" href="site_settings.php"><i class="fas fa-cog"></i> Site Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_drive.php"><i class="fab fa-google-drive"></i> Manage Drive</a>
        </li>
        <li class="nav-item mt-auto">
            <a class="nav-link" href="../index.php" target="_blank"><i class="fas fa-home"></i> View Site</a>
            <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</div>

<div class="content-wrapper" id="content-wrapper">
    <header class="admin-header">
        <button class="sidebar-toggle-btn" id="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <h4 class="ms-3 mb-0">Admin Menu</h4>
    </header>
    <div class="container-fluid pt-3">
