<?php
$pageTitle = "My Profile";
require_once __DIR__ . '/includes/config.php';

// --- Auth Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/header.php';

// Include the correct navbar based on user role
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4) { // Assuming 4 is the role_id for students
    include __DIR__ . '/includes/student_navbar.php';
} else {
    // Optional: handle other roles or redirect
}

?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>My Profile</h2>
                </div>
                <div class="card-body">
                    <p>This page is currently under construction. Your profile details will be available here soon.</p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
