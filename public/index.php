<?php
// public/index.php - Front Controller

session_start();

// Require essential files
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../src/controllers/UserController.php';
require_once __DIR__ . '/../src/controllers/ProductController.php';
require_once __DIR__ . '/../src/controllers/DashboardController.php';
require_once __DIR__ . '/../src/controllers/CartController.php';
require_once __DIR__ . '/../src/controllers/OrderController.php';
require_once __DIR__ . '/../src/controllers/PaymentController.php';
require_once __DIR__ . '/../src/controllers/DownloadController.php';
require_once __DIR__ . '/../src/models/Category.php';
require_once __DIR__ . '/../src/models/Order.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/License.php';

// Get the database connection
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --- Routing ---

$request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_path === '') { $request_path = 'home'; }

// Define application routes
$routes = [
    'GET' => [
        'home' => fn() => require_once __DIR__ . '/../src/views/home.php',
        'products' => fn() => require_once __DIR__ . '/../src/views/products.php',
        'products/create' => fn() => (new ProductController($pdo))->create(),
        'product-details' => fn() => (new ProductController($pdo))->show(),
        'login' => fn() => require_once __DIR__ . '/../src/views/auth.php',
        'register' => fn() => require_once __DIR__ . '/../src/views/auth.php',
        'logout' => fn() => (new UserController($pdo))->logout(),
        'dashboard' => fn() => (new DashboardController($pdo))->index(),
        'cart' => fn() => (new CartController($pdo))->index(),
        'checkout' => fn() => (new OrderController($pdo))->checkout(),
        'order/details' => fn() => (new OrderController($pdo))->details(),
        'download' => fn() => (new DownloadController($pdo))->download(),
        'mock/payment/confirm' => fn() => (new PaymentController($pdo))->mockPaymentConfirm(),
        'webhook/payment' => fn() => (new PaymentController($pdo))->webhook(),
        'api/search' => fn() => (new ProductController($pdo))->search(),
    ],
    'POST' => [
        'register' => fn() => (new UserController($pdo))->register(),
        'login' => fn() => (new UserController($pdo))->login(),
        'products/create' => fn() => (new ProductController($pdo))->store(),
        'cart/add' => fn() => (new CartController($pdo))->add(),
        'cart/remove' => fn() => (new CartController($pdo))->remove(),
        'orders/create' => fn() => (new OrderController($pdo))->store(),
        'pay/paystack' => fn() => (new PaymentController($pdo))->payWithPaystack(),
        'pay/flutterwave' => fn() => (new PaymentController($pdo))->payWithFlutterwave(),
    ]
];

// Define protected routes and their required roles
$protected_routes = [
    'dashboard' => ['buyer', 'seller'],
    'products/create' => ['seller'],
    'cart' => ['buyer', 'seller'],
    'cart/add' => ['buyer', 'seller'],
    'cart/remove' => ['buyer', 'seller'],
    'orders/create' => ['buyer', 'seller'],
    'checkout' => ['buyer', 'seller'],
    'order/details' => ['buyer'],
    'download' => ['buyer'],
    'pay/paystack' => ['buyer', 'seller'],
    'pay/flutterwave' => ['buyer', 'seller'],
    'mock/payment/confirm' => ['buyer', 'seller'],
];

// Check for protected route and authentication
if (array_key_exists($request_path, $protected_routes)) {
    if (!Session::has('user_id')) {
        Session::flash('error_message', 'You must be logged in to perform that action.');
        header('Location: /login');
        exit();
    }

    $required_roles = $protected_routes[$request_path];
    $user_role = Session::get('user_type');
    if (!in_array($user_role, $required_roles)) {
        Session::flash('error_message', 'You do not have permission to access that page.');
        header('Location: /dashboard');
        exit();
    }
}

// Route the request
if (isset($routes[$request_method][$request_path])) {
    $action = $routes[$request_method][$request_path];
    $action();
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you are looking for does not exist.</p>";
}
