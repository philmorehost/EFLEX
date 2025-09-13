<?php
$pageTitle = "Profile";
require_once __DIR__ . '/includes/config.php';

// Student-only page
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/student_navbar.php';
require_once __DIR__ . '/includes/header.php';

?>

<div class="container mt-5">
    <h1>Student Profile</h1>
    <p>This is the student profile page. It is currently under construction.</p>
    <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
