<?php
/**
 * LUNIX WEAR - Reviews API Endpoint
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
        case 'list':
        case 'get':
            $query = 'SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.is_approved = 1';
            $types = '';
            $params = [];

            if (isset($_GET['product_id'])) {
                $query .= ' AND r.product_id = ?';
                $types .= 'i';
                $params[] = $_GET['product_id'];
            }

            $query .= ' ORDER BY r.created_at DESC';
            
            if (isset($_GET['limit'])) {
                $query .= ' LIMIT ?';
                $types .= 'i';
                $params[] = $_GET['limit'];
            }

            $db->prepare($query);
            foreach ($params as $key => $value) {
                $db->bind($key + 1, $value);
            }
            $db->execute();

            $reviews = $db->fetchAll();
            $response = ['success' => true, 'reviews' => $reviews];
            break;

        case 'submit':
            if (!isLoggedIn()) {
                $response = ['success' => false, 'message' => 'Please login to submit a review'];
                break;
            }

            if (!isset($_POST['product_id'], $_POST['rating'], $_POST['content'])) {
                $response = ['success' => false, 'message' => 'Required fields missing'];
                break;
            }

            // Check if already reviewed
            $db->prepare('SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1');
            $db->bind(1, (int)$_POST['product_id'], 'i');
            $db->bind(2, getCurrentUserId(), 'i');
            $db->execute();

            if ($db->getResult()->num_rows > 0) {
                $response = ['success' => false, 'message' => 'You have already reviewed this product'];
                break;
            }

            // Check if purchased
            $db->prepare(`
                SELECT o.id FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
                LIMIT 1
            `);
            $db->bind(1, getCurrentUserId(), 'i');
            $db->bind(2, (int)$_POST['product_id'], 'i');
            $db->execute();
            $orderResult = $db->getResult();

            $db->prepare(`
                INSERT INTO reviews (product_id, user_id, rating, content, is_verified_purchase, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            `);
            $db->bind(1, (int)$_POST['product_id'], 'i');
            $db->bind(2, getCurrentUserId(), 'i');
            $db->bind(3, (int)$_POST['rating'], 'i');
            $db->bind(4, sanitize($_POST['content']), 's');
            $db->bind(5, $orderResult->num_rows > 0 ? 1 : 0, 'i');

            if ($db->execute()) {
                $response = ['success' => true, 'message' => 'Review submitted successfully. It will be displayed after approval.'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to submit review'];
            }
            break;
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit();

?>