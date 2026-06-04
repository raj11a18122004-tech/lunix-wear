<?php
/**
 * LUNIX WEAR - Products API Endpoint
 */

header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/Product.php';

if (!isset($_SESSION)) {
    session_start();
}

$product = new Product($db);
$action = $_GET['action'] ?? 'list';
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    switch ($action) {
        case 'list':
        case 'get':
            $filters = [
                'category_id' => $_GET['category_id'] ?? null,
                'search' => $_GET['search'] ?? null,
                'min_price' => $_GET['min_price'] ?? null,
                'max_price' => $_GET['max_price'] ?? null,
                'featured' => $_GET['featured'] ?? false,
                'best_seller' => $_GET['best_seller'] ?? false,
                'new_arrival' => $_GET['new_arrival'] ?? false,
                'limit' => $_GET['limit'] ?? 12
            ];

            // Remove null values
            $filters = array_filter($filters, function($value) { return $value !== null && $value !== ''; });

            $products = $product->getProducts($filters);
            $response = ['success' => true, 'products' => $products];
            break;

        case 'detail':
            if (!isset($_GET['id'])) {
                $response = ['success' => false, 'message' => 'Product ID required'];
                break;
            }
            
            $productData = $product->getProductById($_GET['id']);
            if ($productData) {
                $response = ['success' => true, 'product' => $productData];
            } else {
                $response = ['success' => false, 'message' => 'Product not found'];
            }
            break;
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit();

?>