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

    case 'toggle_wishlist':
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            $response['message'] = 'User not logged in.';
            $response['status'] = 'login_required';
            break;
        }

        $product_id = $data['product_id'] ?? 0;
        $user_id = $_SESSION['id'];

        if ($product_id > 0) {
            // Check if item is already in wishlist
            $stmt_check = $mysqli->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt_check->bind_param("ii", $user_id, $product_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if($stmt_check->num_rows > 0) {
                // Remove from wishlist
                $stmt_remove = $mysqli->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $stmt_remove->bind_param("ii", $user_id, $product_id);
                $stmt_remove->execute();
                $response = ['status' => 'success', 'action' => 'removed'];
            } else {
                // Add to wishlist
                $stmt_add = $mysqli->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $stmt_add->bind_param("ii", $user_id, $product_id);
                $stmt_add->execute();
                $response = ['status' => 'success', 'action' => 'added'];
            }
        } else {
            $response['message'] = 'Invalid product ID.';
        }
        break;

    case 'product_search':
        $query = $data['query'] ?? '';
        $products = [];
        if(!empty($query)){
            $sql = "SELECT id, name, image, price FROM products WHERE name LIKE ? LIMIT 10";
            if($stmt = $mysqli->prepare($sql)){
                $search_query = "%" . $query . "%";
                $stmt->bind_param("s", $search_query);
                $stmt->execute();
                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
                    $products[] = $row;
                }
                $stmt->close();
            }
        }
        $response = ['status' => 'success', 'products' => $products];
        break;

    default:
        // Keep the default invalid request message
        break;
}

// Echo the JSON response
echo json_encode($response);
exit();
?>
