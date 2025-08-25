<?php
// Set the content type to JSON
header('Content-Type: application/json');

// We need to start the session to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// Initialize the cart session if it doesn't exist
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

$response = ['status' => 'error', 'message' => 'Invalid request.'];

// Determine the action from either JSON payload or POST data
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['action'])) {
        $action = $_POST['action'];
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
    }
}


switch ($action) {
    case 'add_to_cart':
        $product_id = $data['product_id'] ?? 0;
        $quantity = $data['quantity'] ?? 1;

        if ($product_id > 0 && $quantity > 0) {
            if(isset($_SESSION['cart'][$product_id])){
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            $cart_item_count = array_sum($_SESSION['cart']);
            $response = [
                'status' => 'success',
                'message' => 'Item added to cart.',
                'cart_count' => $cart_item_count
            ];
        } else {
            $response['message'] = 'Invalid product ID or quantity.';
        }
        break;

    case 'save_onesignal_player_id':
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            $response['message'] = 'User not logged in.';
            break;
        }

        $player_id = $_POST['player_id'] ?? '';
        $user_id = $_SESSION['id'];

        if (!empty($player_id) && !empty($user_id)) {
            $sql = "UPDATE users SET onesignal_player_id = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("si", $player_id, $user_id);
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Player ID saved.'];
                } else {
                    $response['message'] = 'Failed to save Player ID.';
                }
                $stmt->close();
            }
        } else {
            $response['message'] = 'Invalid Player ID or User ID.';
        }
        break;

    default:
        // Keep the default invalid request message
        break;
}

// Echo the JSON response
echo json_encode($response);
exit();
?>
