<?php
// Initialize session and check login status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'includes/db_connect.php';

// --- Local File Subscription Logic ---
$user_id = $_SESSION['id'];
$subscribed_classes = [];
$files_for_class = [];
$class_name = '';
$view_file_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;

// 1. Get all active subscriptions for the user.
// The check for whether a subscription has content is handled by the UI logic later on.
$sql_sub = "SELECT DISTINCT p.id, p.name
            FROM user_subscriptions us
            JOIN products p ON us.product_id = p.id
            WHERE us.user_id = ?
              AND us.status = 'active'
              AND (us.expires_at IS NULL OR us.expires_at >= CURDATE())";

if ($stmt_sub = $mysqli->prepare($sql_sub)) {
    $stmt_sub->bind_param("i", $user_id);
    $stmt_sub->execute();
    $result = $stmt_sub->get_result();
    $subscribed_classes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt_sub->close();
}

if (empty($subscribed_classes)) {
    header("location: subscribe.php");
    exit;
}

// 2. If a class is selected, get its details (files and/or HTML content)
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$html_content = null;
if ($class_id) {
    $is_subscribed_to_class = false;
    foreach($subscribed_classes as $p) {
        if ($p['id'] == $class_id) {
            $is_subscribed_to_class = true;
            $class_name = $p['name'];
            break;
        }
    }

    if ($is_subscribed_to_class) {
        // Check for HTML content first
        $sql_html = "SELECT html_content FROM products WHERE id = ?";
        if($stmt_html = $mysqli->prepare($sql_html)) {
            $stmt_html->bind_param("i", $class_id);
            $stmt_html->execute();
            $result_html = $stmt_html->get_result();
            if($row = $result_html->fetch_assoc()) {
                $html_content = $row['html_content'];
            }
            $stmt_html->close();
        }

        // Then check for local files
        $sql_files = "SELECT id, original_filename FROM product_local_files WHERE product_id = ? ORDER BY original_filename ASC";
        if($stmt_files = $mysqli->prepare($sql_files)) {
            $stmt_files->bind_param("i", $class_id);
            $stmt_files->execute();
            $result_files = $stmt_files->get_result();
            $files_for_class = $result_files->fetch_all(MYSQLI_ASSOC);
            $stmt_files->close();
        }

        // And also check for Google Drive files
        $gdrive_files_for_class = [];
        $sql_gdrive_files = "SELECT gdrive_file_id, filename, webview_link FROM product_gdrive_files WHERE product_id = ? ORDER BY filename ASC";
        if($stmt_gdrive = $mysqli->prepare($sql_gdrive_files)) {
            $stmt_gdrive->bind_param("i", $class_id);
            $stmt_gdrive->execute();
            $result_gdrive = $stmt_gdrive->get_result();
            $gdrive_files_for_class = $result_gdrive->fetch_all(MYSQLI_ASSOC);
            $stmt_gdrive->close();
        }

    } else {
        // User is not subscribed to the selected class, reset it.
        $class_id = 0;
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <h4>My Subscriptions</h4>
            <div class="list-group mb-4">
                <?php foreach ($subscribed_classes as $class) : ?>
                    <a href="lesson_notes.php?class_id=<?php echo $class['id']; ?>"
                       class="list-group-item list-group-item-action <?php echo ($class_id == $class['id']) ? 'active' : ''; ?>">
                        <i class="fas fa-book me-2"></i> <?php echo htmlspecialchars($class['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($class_id && !empty($files_for_class)) : ?>
                <h4 class="mt-4">Local Files for <?php echo htmlspecialchars($class_name); ?></h4>
                <div class="list-group">
                    <?php foreach ($files_for_class as $file) : ?>
                        <a href="lesson_notes.php?class_id=<?php echo $class_id; ?>&view=<?php echo $file['id']; ?>" class="list-group-item list-group-item-action <?php echo ($view_file_id == $file['id']) ? 'active' : ''; ?>">
                            <i class="fas fa-file-alt me-2"></i> <?php echo htmlspecialchars($file['original_filename']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($class_id && !empty($gdrive_files_for_class)) : ?>
                <h4 class="mt-4">Google Drive Files for <?php echo htmlspecialchars($class_name); ?></h4>
                <div class="list-group">
                    <?php foreach ($gdrive_files_for_class as $file) : ?>
                        <a href="<?php echo htmlspecialchars($file['webview_link']); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fab fa-google-drive me-2"></i> <?php echo htmlspecialchars($file['filename']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <?php if ($view_file_id) : ?>
                <div class="embed-responsive" style="height: 80vh; border: 1px solid #ddd;">
                    <iframe class="embed-responsive-item w-100 h-100" src="view_file.php?id=<?php echo $view_file_id; ?>"></iframe>
                </div>
            <?php elseif ($class_id && !empty($html_content)) : ?>
                <h4><?php echo htmlspecialchars($class_name); ?></h4>
                <div id="secure-content" class="secure-content">
                    <?php echo $html_content; ?>
                </div>
            <?php else : ?>
                <div class="text-center p-5 border rounded d-flex flex-column justify-content-center align-items-center" style="min-height: 300px;">
                    <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                    <h4>Welcome to your Lesson Notes</h4>
                    <p class="text-muted">Select a subscription from the list on the left to view its content.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const secureContent = document.getElementById('secure-content');

// --- Disable Right-Click ---
if (secureContent) {
    secureContent.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
}

// --- Disable Keyboard Shortcuts for Dev Tools & Saving ---
document.addEventListener('keydown', function(e) {
    // F12
    if (e.key === 'F12') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+Shift+I
    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+Shift+J
    if (e.ctrlKey && e.shiftKey && e.key === 'J') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+U
    if (e.ctrlKey && e.key === 'u') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+S
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
