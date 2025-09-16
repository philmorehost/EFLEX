<?php
// public/index.php - Front Controller

// Require essential files
require_once __DIR__ . '/../src/lib/Database.php';
require_once __DIR__ . '/../src/controllers/UserController.php';

// Get the database connection
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 1. Get request path and method
$request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$request_method = $_SERVER['REQUEST_METHOD'];

// If the path is empty, it's the homepage
if ($request_path === '') {
    $request_path = 'home';
}

// 2. Define application routes
$routes = [
    'GET' => [
        'home' => __DIR__ . '/../src/views/home.php',
        'products' => __DIR__ . '/../src/views/products.php',
        'product-details' => __DIR__ . '/../src/views/product_details.php',
        'login' => __DIR__ . '/../src/views/auth.php',
        'register' => __DIR__ . '/../src/views/auth.php',
    ],
    'POST' => [
        'register' => function() use ($pdo) {
            $controller = new UserController($pdo);
            $controller->register();
        },
        // Add other POST routes here later (e.g., login)
    ]
];

// 3. Route the request
if (isset($routes[$request_method][$request_path])) {
    $route = $routes[$request_method][$request_path];
    if (is_callable($route)) {
        // If the route is a function (a controller action)
        $route();
    } else {
        // If the route is a path to a view file
        require_once $route;
    }
} else {
    // Handle 404 Not Found
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you are looking for does not exist.</p>";
}
