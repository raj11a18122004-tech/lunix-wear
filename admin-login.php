<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Auth.php';

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin')) {
    redirect('admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth($db);
    $result = $auth->adminLogin($_POST['email'] ?? '', $_POST['password'] ?? '');
    
    if ($result['success']) {
        redirect('admin/');
    } else {
        $error = $result['message'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LUNIX WEAR</title>
    <style>
        body { font-family: Arial; background: #000; color: #D4AF37; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: white; color: #333; padding: 40px; border-radius: 8px; width: 100%; max-width: 400px; }
        h1 { color: #D4AF37; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #D4AF37; color: black; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>LUNIX WEAR Admin</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>