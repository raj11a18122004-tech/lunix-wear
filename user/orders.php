<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/Order.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isLoggedIn()) {
    redirect('login');
}

$userId = getCurrentUserId();
$pageTitle = 'My Orders';

$order = new Order($db);
$orders = $order->getUserOrders($userId, 50, 0);

include '../includes/header.php';
?>

<main class="user-orders-page" style="padding: 40px 0; min-height: calc(100vh - 300px);">
    <div class="container">
        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 30px;">
            
            <!-- Sidebar -->
            <aside style="background: white; padding: 20px; border-radius: 8px; height: fit-content;">
                <h3 style="margin-bottom: 20px;">My Account</h3>
                <nav style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo BASE_URL; ?>user/profile" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Profile</a>
                    <a href="<?php echo BASE_URL; ?>user/orders" style="padding: 12px; background: #D4AF37; color: black; border-radius: 4px; text-decoration: none; font-weight: 600;">Orders</a>
                    <a href="<?php echo BASE_URL; ?>user/addresses" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Addresses</a>
                    <a href="<?php echo BASE_URL; ?>user/wishlist" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Wishlist</a>
                    <a href="<?php echo BASE_URL; ?>logout" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Logout</a>
                </nav>
            </aside>
            
            <!-- Orders Content -->
            <section style="background: white; padding: 40px; border-radius: 8px;">
                <h2 style="margin-bottom: 30px;">Your Orders</h2>
                
                <?php if (count($orders) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f9f9f9; border-bottom: 2px solid #ddd;">
                                <th style="padding: 15px; text-align: left;">Order #</th>
                                <th style="padding: 15px; text-align: left;">Date</th>
                                <th style="padding: 15px; text-align: left;">Items</th>
                                <th style="padding: 15px; text-align: left;">Total</th>
                                <th style="padding: 15px; text-align: left;">Status</th>
                                <th style="padding: 15px; text-align: left;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $ord): ?>
                                <tr style="border-bottom: 1px solid #ddd;">
                                    <td style="padding: 15px; font-weight: 600;"><?php echo htmlspecialchars($ord['order_number']); ?></td>
                                    <td style="padding: 15px;"><?php echo formatDate($ord['created_at']); ?></td>
                                    <td style="padding: 15px;">-</td>
                                    <td style="padding: 15px; color: #D4AF37; font-weight: 600;">₹<?php echo number_format($ord['total'], 2); ?></td>
                                    <td style="padding: 15px;">
                                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; 
                                            background: <?php 
                                                if ($ord['status'] === 'delivered') echo '#d1e7dd'; 
                                                elseif ($ord['status'] === 'shipped') echo '#cfe2ff'; 
                                                elseif ($ord['status'] === 'processing') echo '#d1ecf1'; 
                                                else echo '#fff3cd'; 
                                            ?>; 
                                            color: <?php 
                                                if ($ord['status'] === 'delivered') echo '#0f5132'; 
                                                elseif ($ord['status'] === 'shipped') echo '#084298'; 
                                                elseif ($ord['status'] === 'processing') echo '#0c5460'; 
                                                else echo '#856404'; 
                                            ?>;">
                                            <?php echo ucfirst($ord['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <a href="<?php echo BASE_URL; ?>user/order-details?id=<?php echo $ord['id']; ?>" style="color: #D4AF37; text-decoration: none; font-weight: 600;">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 8px;">
                        <h3 style="color: #999;">No orders yet</h3>
                        <p style="color: #999; margin-bottom: 20px;">Start shopping to create your first order</p>
                        <a href="<?php echo BASE_URL; ?>products" class="btn" style="display: inline-block;">Shop Now</a>
                    </div>
                <?php endif; ?>
                
            </section>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
