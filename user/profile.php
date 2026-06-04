<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isLoggedIn()) {
    redirect('login');
}

$userId = getCurrentUserId();
$pageTitle = 'My Profile';

$db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$db->bind(1, $userId, 'i');
$db->execute();
$user = $db->fetch();

include '../includes/header.php';
?>

<main class="user-profile-page" style="padding: 40px 0; min-height: calc(100vh - 300px);">
    <div class="container">
        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 30px;">
            
            <!-- Sidebar -->
            <aside style="background: white; padding: 20px; border-radius: 8px; height: fit-content;">
                <h3 style="margin-bottom: 20px;">My Account</h3>
                <nav style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo BASE_URL; ?>user/profile" style="padding: 12px; background: #D4AF37; color: black; border-radius: 4px; text-decoration: none; font-weight: 600;">Profile</a>
                    <a href="<?php echo BASE_URL; ?>user/orders" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Orders</a>
                    <a href="<?php echo BASE_URL; ?>user/addresses" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Addresses</a>
                    <a href="<?php echo BASE_URL; ?>user/wishlist" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Wishlist</a>
                    <a href="<?php echo BASE_URL; ?>logout" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Logout</a>
                </nav>
            </aside>
            
            <!-- Profile Content -->
            <section style="background: white; padding: 40px; border-radius: 8px;">
                <h2 style="margin-bottom: 30px;">Profile Information</h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Name</label>
                        <p style="padding: 12px; background: #f9f9f9; border-radius: 4px;"><?php echo htmlspecialchars($user['name']); ?></p>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Email</label>
                        <p style="padding: 12px; background: #f9f9f9; border-radius: 4px;"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Phone</label>
                        <p style="padding: 12px; background: #f9f9f9; border-radius: 4px;"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Member Since</label>
                        <p style="padding: 12px; background: #f9f9f9; border-radius: 4px;"><?php echo formatDate($user['created_at']); ?></p>
                    </div>
                </div>
                
                <hr style="margin: 40px 0; border: none; border-top: 1px solid #ddd;">
                
                <h3 style="margin-bottom: 20px;">Email Verification</h3>
                <?php if ($user['email_verified']): ?>
                    <p style="color: #28a745; font-weight: 600;">✓ Email verified</p>
                <?php else: ?>
                    <p style="color: #dc3545;">Email not verified. <a href="<?php echo BASE_URL; ?>user/verify-email" style="color: #D4AF37;">Send verification link</a></p>
                <?php endif; ?>
                
            </section>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
