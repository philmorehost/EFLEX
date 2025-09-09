<?php
session_start();

// The header file contains the primary session check and the top part of the page layout.
require_once __DIR__ . '/../templates/header.php';

// The dashboard_cards file contains the main content for this specific page.
require_once __DIR__ . '/../templates/dashboard_cards.php';

// The footer file closes the layout and includes necessary JavaScript.
require_once __DIR__ . '/../templates/footer.php';

?>
