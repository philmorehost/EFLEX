<?php
$pageTitle = "Knowledge Base";
require_once __DIR__ . '/includes/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role_id = $_SESSION['role_id'];
$is_admin_role = in_array($role_id, [1, 2, 3]);

// --- Page-specific logic and data fetching ---
$guide_file = '';
switch ($role_id) {
    case 1: $guide_file = __DIR__ . '/includes/kb/super_admin_guide.php'; break;
    case 2: $guide_file = __DIR__ . '/includes/kb/admin_guide.php'; break;
    case 3: $guide_file = __DIR__ . '/includes/kb/staff_guide.php'; break;
    case 4: $guide_file = __DIR__ . '/includes/kb/user_guide.php'; break;
}

// --- Start HTML Output ---
require_once __DIR__ . '/includes/header.php';

if ($is_admin_role) {
    // Admin layout with sidebar
    echo '<div class="d-flex" id="admin-wrapper">';
    include __DIR__ . '/includes/admin_sidebar.php';
    echo '<div id="page-content-wrapper">';
    echo '<nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">';
    echo '<div class="d-flex align-items-center"><i class="bi bi-list fs-4 me-3" id="menu-toggle"></i><h2 class="fs-2 m-0">Knowledge Base</h2></div>';
    include __DIR__ . '/includes/admin_navbar_user.php';
    echo '</nav>';
    echo '<div class="container-fluid px-4">';
} else {
    // Standard user layout
    echo '<div class="container mt-5">';
    echo '<h1 class="mb-4">Knowledge Base</h1>';
}

if (!empty($guide_file) && file_exists($guide_file)) {
    include $guide_file;
} else {
    echo '<div class="alert alert-warning">The guide for your user role could not be found.</div>';
}

if (!$is_admin_role) {
    // "Back to Dashboard" button for non-admin users
    echo '<div class="mt-4"><a href="index.php" class="btn btn-secondary">&laquo; Back to Dashboard</a></div>';
}

// Close the layout divs
echo '</div>'; // Closes .container-fluid or .container
if ($is_admin_role) {
    echo '</div>'; // Closes #page-content-wrapper
    echo '</div>'; // Closes #admin-wrapper
}

require_once __DIR__ . '/includes/footer.php';
?>
