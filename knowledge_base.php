<?php
$pageTitle = "Knowledge Base";
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Determine which guide to show based on the user's role_id
$role_id = $_SESSION['role_id'];
$guide_file = '';

switch ($role_id) {
    case 1: // Super Admin
        $guide_file = __DIR__ . '/includes/kb/super_admin_guide.php';
        break;
    case 2: // Admin
        $guide_file = __DIR__ . '/includes/kb/admin_guide.php';
        break;
    case 3: // Staff
        $guide_file = __DIR__ . '/includes/kb/staff_guide.php';
        break;
    case 4: // User
        $guide_file = __DIR__ . '/includes/kb/user_guide.php';
        break;
    default:
        // A fallback for any undefined role
        echo "<p>No guide available for your user level.</p>";
        break;
}
?>

<div class="container mt-5">
    <h1 class="mb-4">Knowledge Base</h1>

    <?php
    if (!empty($guide_file) && file_exists($guide_file)) {
        include $guide_file;
    } else {
        // This will show if the switch statement didn't find a match
        echo '<div class="alert alert-warning">The guide for your user role could not be found.</div>';
    }
    ?>

    <div class="mt-4">
        <a href="index.php" class="btn btn-secondary">&laquo; Back to Dashboard</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
