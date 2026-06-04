<?php
/**
/**
 * LUNIX WEAR - Newsletter API Endpoint
 */

header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION)) {
    session_start();
}

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['email']) || !isValidEmail($data['email'])) {
        $response = ['success' => false, 'message' => 'Invalid email address'];
    } else {
        $email = sanitize($data['email']);
        
        // Check if already subscribed
        $db->prepare('SELECT id FROM newsletter WHERE email = ? LIMIT 1');
        $db->bind(1, $email, 's');
        $db->execute();
        
        if ($db->getResult()->num_rows > 0) {
            $response = ['success' => false, 'message' => 'Already subscribed'];
        } else {
            // Add to newsletter
            $db->prepare('INSERT INTO newsletter (email, is_active, subscribed_at) VALUES (?, 1, NOW())');
            $db->bind(1, $email, 's');
            
            if ($db->execute()) {
                $response = ['success' => true, 'message' => 'Successfully subscribed to newsletter'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to subscribe'];
            }
        }
    }
}

echo json_encode($response);
exit();

?>