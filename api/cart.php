<?php
/**
 * LUNIX WEAR - Cart API Endpoint
 */

header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION)) {
    session_start();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    switch ($action) {
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($data['product_id']) || !isset($data['quantity'])) {
                    $response = ['success' => false, 'message' => 'Invalid product data'];
                    break;
                }

                addToCart($data['product_id'], $data['quantity'], $data['variant'] ?? []);
                $response = ['success' => true, 'message' => 'Product added to cart', 'cart_count' => count(getCart())];
            }
            break;

        case 'remove':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                removeFromCart($data['cart_key']);
                $response = ['success' => true, 'message' => 'Product removed from cart', 'cart_count' => count(getCart())];
            }
            break;

        case 'get':
            $cart = getCart();
            $response = ['success' => true, 'cart' => $cart, 'count' => count($cart)];
            break;

        case 'clear':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                clearCart();
                $response = ['success' => true, 'message' => 'Cart cleared'];
            }
            break;
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit();

?>