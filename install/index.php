<?php
// CBT Platform Multi-Stage Installer

// --- Session Start ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Styling for Installer ---
$style = "
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; margin: 0; background-color: #f4f5f7; }
    .container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #333; }
    .btn { display: inline-block; background-color: #007bff; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; font-weight: bold; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    code { background: #eee; padding: 2px 4px; border-radius: 4px; }
    ul { list-style-type: none; padding-left: 0; }
    li { margin-bottom: 10px; padding: 10px; border-radius: 5px; }
    .pass { background-color: #d4edda; border-left: 5px solid #28a745; }
    .fail { background-color: #f8d7da; border-left: 5px solid #dc3545; }
    form div { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type='text'], input[type='password'] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
";

// --- Installer Action Handler ---
$action = $_GET['action'] ?? null;

// Handle form submission from Stage 2
if ($action === 'savedb' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['db_details'] = [
        'host' => $_POST['db_host'],
        'name' => $_POST['db_name'],
        'user' => $_POST['db_user'],
        'pass' => $_POST['db_pass']
    ];
    $_SESSION['install_stage'] = 3; // Set stage for installation
    header('Location: index.php');
    exit;
}

// Handle advancing from Stage 1 to 2
if ($action === 'next_stage') {
    if (isset($_SESSION['install_stage']) && $_SESSION['install_stage'] == 1) {
        $_SESSION['install_stage'] = 2;
    }
    header('Location: index.php');
    exit;
}

// Handle user clicking "Back" from Stage 2
if ($action === 'reset') {
    $_SESSION['install_stage'] = 1;
    unset($_SESSION['db_details']); // Clear stored DB details
    unset($_SESSION['db_error']);
    header('Location: index.php');
    exit;
}


// --- Installer Router ---
if (!isset($_SESSION['install_stage'])) {
    $_SESSION['install_stage'] = 1;
}
$stage = $_SESSION['install_stage'];

$stages = [
    1 => 'stage1_requirements.php',
    2 => 'stage2_database.php',
    3 => 'stage3_install.php',
    4 => 'stage4_success.php',
];

$stage_file = $stages[$stage] ?? $stages[1];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CBT Platform Installation</title>
    <style><?php echo $style; ?></style>
</head>
<body>
    <div class="container">
        <?php
        if (file_exists($stage_file)) {
            include $stage_file;
        } else {
            echo "<div class='alert alert-danger'><strong>Error:</strong> The required installation file (<code>" . htmlspecialchars($stage_file) . "</code>) could not be found.</div>";
        }
        ?>
    </div>
</body>
</html>
