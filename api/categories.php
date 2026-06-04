<?php
/**
 * LUNIX WEAR - Categories API Endpoint
 */

header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION)) {
    session_start();
}

$db->prepare('SELECT id, name, slug, description, image, icon FROM categories WHERE is_active = 1 ORDER BY display_order ASC');
$db->execute();
$categories = $db->fetchAll();

echo json_encode(['success' => true, 'categories' => $categories]);
exit();

?>