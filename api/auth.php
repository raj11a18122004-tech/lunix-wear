<?php
/**
 * LUNIX WEAR - Authentication API Endpoint
 */

header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/Auth.php';

if (!isset($_SESSION)) {
    session_start();
}

$auth = new Auth($db);
$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    switch ($action) {
        case 'register':
            if (!isset($_POST['name'], $_POST['email'], $_POST['password'])) {
                $response = ['success' => false, 'message' => 'Required fields missing'];
                break;
            }

            if (!isValidEmail($_POST['email'])) {
                $response = ['success' => false, 'message' => 'Invalid email format'];
                break;
            }

            $response = $auth->register(
                sanitize($_POST['name']),
                sanitize($_POST['email']),
                $_POST['password'],
                sanitize($_POST['phone'] ?? '')
            );
            break;

        case 'login':
            if (!isset($_POST['email'], $_POST['password'])) {
                $response = ['success' => false, 'message' => 'Email and password required'];
                break;
            }

            $response = $auth->login(
                sanitize($_POST['email']),
                $_POST['password']
            );
            break;

        case 'admin_login':
            if (!isset($_POST['email'], $_POST['password'])) {
                $response = ['success' => false, 'message' => 'Email and password required'];
                break;
            }

            $response = $auth->adminLogin(
                sanitize($_POST['email']),
                $_POST['password']
            );
            break;

        case 'logout':
            $auth->logout();
            $response = ['success' => true, 'message' => 'Logged out successfully'];
            break;

        case 'forgot_password':
            if (!isset($_POST['email'])) {
                $response = ['success' => false, 'message' => 'Email required'];
                break;
            }

            $response = $auth->sendResetEmail(sanitize($_POST['email']));
            break;
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit();

?>