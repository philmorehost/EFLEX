<?php
// public/index.php - Front Controller

// Start the session
session_start();

// Require essential files
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../src/controllers/UserController.php';
require_once __DIR__ . '/../src/controllers/ProductController.php';
require_once __DIR__ . '/../src/controllers/DashboardController.php';
require_once __DIR__ . '/../src/models/Category.php';

// Get the database connection
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --- Routing ---

// 1. Get request path and method
$request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_path === '') {
    $request_path = 'home';
}

// 2. Define application routes
$routes = [
    'GET' => [
        'home' => fn() => require_once __DIR__ . '/../src/views/home.php',
        'products' => fn() => require_once __DIR__ . '/../src/views/products.php',
        'products/create' => fn() => (new ProductController($pdo))->create(),
        'product-details' => fn() => require_once __DIR__ . '/../src/views/product_details.php',
        'login' => fn() => require_once __DIR__ . '/../src/views/auth.php',
        'register' => fn() => require_once __DIR__ . '/../src/views/auth.php',
        'logout' => fn() => (new UserController($pdo))->logout(),
        'dashboard' => fn() => (new DashboardController($pdo))->index(),
    ],
    'POST' => [
        'register' => fn() => (new UserController($pdo))->register(),
        'login' => fn() => (new UserController($pdo))->login(),
        'products/create' => fn() => (new ProductController($pdo))->store(),
    ]
];

// 3. Define protected routes and their required roles
$protected_routes = [
    'dashboard' => ['buyer', 'seller'],
    'products/create' => ['seller'],
];

// 4. Check for protected route and authentication
if (array_key_exists($request_path, $protected_routes)) {
    // Check if user is logged in
    if (!Session::has('user_id')) {
        Session::flash('error_message', 'You must be logged in to view that page.');
        header('Location: /login');
        exit();
    }

    // Check if user has the required role
    $required_roles = $protected_routes[$request_path];
    $user_role = Session::get('user_type');
    if (!in_array($user_role, $required_roles)) {
        Session::flash('error_message', 'You do not have permission to access that page.');
        // Redirect to a safe page, like the dashboard
        header('Location: /dashboard');
        exit();
    }
}

// 5. Route the request
if (isset($routes[$request_method][$request_path])) {
    $action = $routes[$request_method][$request_path];
    $action();
} else {
    // Handle 404 Not Found
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you are looking for does not exist.</p>";
}
