<?php
/**
 * LUNIX WEAR - Common Functions
 * Utility functions used throughout the application
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate unique token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Check user role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect to page
 */
function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit();
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes, $maxSize = MAX_FILE_SIZE) {
    $errors = [];

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed.';
    }

    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds maximum limit (' . formatBytes($maxSize) . ')';
    }

    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedTypes)) {
        $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
    }

    return $errors;
}

/**
 * Format bytes to readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'INR') {
    $symbols = [
        'INR' => '₹',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£'
    ];
    $symbol = isset($symbols[$currency]) ? $symbols[$currency] : $currency;
    return $symbol . ' ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Generate slug from string
 */
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Check if product exists
 */
function productExists($productId) {
    global $db;
    $db->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
    $db->bind(1, $productId, 'i');
    $db->execute();
    $result = $db->getResult();
    return $result->num_rows > 0;
}

/**
 * Get product by ID
 */
function getProduct($productId) {
    global $db;
    $db->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $db->bind(1, $productId, 'i');
    $db->execute();
    return $db->fetch();
}

/**
 * Get user by ID
 */
function getUser($userId) {
    global $db;
    $db->prepare('SELECT id, name, email, phone, role, created_at FROM users WHERE id = ? LIMIT 1');
    $db->bind(1, $userId, 'i');
    $db->execute();
    return $db->fetch();
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    global $db;
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $db->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $db->bind(1, $userId, 'i');
    $db->bind(2, $action, 's');
    $db->bind(3, $details, 's');
    $db->bind(4, $ip, 's');
    $db->bind(5, $userAgent, 's');
    $db->execute();
}

/**
 * Send email
 */
function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">" . "\r\n";
    }

    return mail($to, $subject, $message, $headers);
}

/**
 * Generate invoice number
 */
function generateInvoiceNumber() {
    return 'LW' . date('Ymd') . str_pad(rand(1, 9999), 5, '0', STR_PAD_LEFT);
}

/**
 * Get cart from session
 */
function getCart() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

/**
 * Add to cart
 */
function addToCart($productId, $quantity = 1, $variant = []) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $cartKey = $productId . '_' . md5(json_encode($variant));
    
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'variant' => $variant
        ];
    }
}

/**
 * Remove from cart
 */
function removeFromCart($cartKey) {
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
}

/**
 * Clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

?>