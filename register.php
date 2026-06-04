<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Auth.php';

if (!isset($_SESSION)) {
    session_start();
}

if (isLoggedIn()) {
    redirect('user/profile');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth($db);
    $result = $auth->register(
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['password'] ?? '',
        $_POST['phone'] ?? ''
    );
    
    if ($result['success']) {
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['role'] = 'customer';
        redirect('user/profile');
    } else {
        $error = $result['message'];
    }
}

include 'includes/header.php';
?>

<main class="register-page" style="padding: 60px 0; min-height: calc(100vh - 300px);">
    <div class="container">
        <div style="max-width: 400px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <h1 style="text-align: center; color: #D4AF37; margin-bottom: 30px;">Create Account</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Full Name</label>
                    <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Email Address</label>
                    <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Phone Number</label>
                    <input type="tel" name="phone" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Password</label>
                    <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #999; display: block; margin-top: 5px;">Minimum 8 characters</small>
                </div>
                
                <button type="submit" class="btn btn-large" style="width: 100%;">Create Account</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: #999;">
                Already have an account? <a href="<?php echo BASE_URL; ?>login" style="color: #D4AF37;">Login here</a>
            </p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
