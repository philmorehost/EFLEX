<?php
// Use require_once to include the functions file.
// This ensures it is loaded only once, preventing fatal errors.
require_once __DIR__ . '/includes/functions.php';

// Now we can safely call the functions.
if (is_logged_in()) {
    $user = get_current_user();
    echo "Welcome, " . htmlspecialchars($user['username']) . "!";
} else {
    echo "Welcome, guest!";
}

// To prove the fix, we can try to include it again.
// With require_once, this line will do nothing and will not cause an error.
require_once __DIR__ . '/includes/functions.php';

echo "<br>Script executed successfully without redeclaration errors.";

?>