<?php
// Set the content type to JSON
header('Content-Type: application/json');

// We need to start the session to access the cart
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize the cart session if it doesn't exist
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = array();
}

$response = ['status' => 'error', 'message' => 'Invalid request.'];

// Check if it's a POST request with an action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'add_to_cart') {
        $product_id = $data['product_id'] ?? 0;
        $quantity = $data['quantity'] ?? 1;

        if ($product_id > 0 && $quantity > 0) {
            // If product is already in cart, update quantity
            if(isset($_SESSION['cart'][$product_id])){
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
            // Else, add new product to cart
                $_SESSION['cart'][$product_id] = $quantity;
            }

            // Calculate new total item count
            $cart_item_count = array_sum($_SESSION['cart']);

            $response = [
                'status' => 'success',
                'message' => 'Item added to cart.',
                'cart_count' => $cart_item_count
            ];
        } else {
            $response['message'] = 'Invalid product ID or quantity.';
        }
    }
}

// Echo the JSON response
echo json_encode($response);
exit();
?>
