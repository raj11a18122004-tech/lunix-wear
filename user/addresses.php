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
$pageTitle = 'My Addresses';
$message = '';

// Handle add/edit address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $db->prepare('INSERT INTO addresses (user_id, full_name, phone, email, address_line_1, address_line_2, city, state, postal_code, address_type, is_default, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $db->bind(1, $userId, 'i');
        $db->bind(2, sanitize($_POST['full_name']), 's');
        $db->bind(3, sanitize($_POST['phone']), 's');
        $db->bind(4, sanitize($_POST['email']), 's');
        $db->bind(5, sanitize($_POST['address_line_1']), 's');
        $db->bind(6, sanitize($_POST['address_line_2'] ?? ''), 's');
        $db->bind(7, sanitize($_POST['city']), 's');
        $db->bind(8, sanitize($_POST['state']), 's');
        $db->bind(9, sanitize($_POST['postal_code']), 's');
        $db->bind(10, $_POST['address_type'] ?? 'both', 's');
        $db->bind(11, isset($_POST['is_default']) ? 1 : 0, 'i');
        
        if ($db->execute()) {
            $message = '<div class="alert alert-success">Address added successfully</div>';
        }
    }
}

$db->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
$db->bind(1, $userId, 'i');
$db->execute();
$addresses = $db->fetchAll();

include '../includes/header.php';
?>

<main class="user-addresses-page" style="padding: 40px 0; min-height: calc(100vh - 300px);">
    <div class="container">
        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 30px;">
            
            <!-- Sidebar -->
            <aside style="background: white; padding: 20px; border-radius: 8px; height: fit-content;">
                <h3 style="margin-bottom: 20px;">My Account</h3>
                <nav style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo BASE_URL; ?>user/profile" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Profile</a>
                    <a href="<?php echo BASE_URL; ?>user/orders" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Orders</a>
                    <a href="<?php echo BASE_URL; ?>user/addresses" style="padding: 12px; background: #D4AF37; color: black; border-radius: 4px; text-decoration: none; font-weight: 600;">Addresses</a>
                    <a href="<?php echo BASE_URL; ?>user/wishlist" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Wishlist</a>
                    <a href="<?php echo BASE_URL; ?>logout" style="padding: 12px; background: white; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none;">Logout</a>
                </nav>
            </aside>
            
            <!-- Addresses Content -->
            <section style="background: white; padding: 40px; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h2 style="margin: 0;">Saved Addresses</h2>
                    <button onclick="toggleAddressForm()" class="btn" style="cursor: pointer;">+ Add New Address</button>
                </div>
                
                <?php echo $message; ?>
                
                <!-- Add Address Form (Hidden by default) -->
                <div id="address-form" style="display: none; background: #f9f9f9; padding: 30px; border-radius: 8px; margin-bottom: 30px;">
                    <h3 style="margin-top: 0;">Add New Address</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Full Name</label>
                                <input type="text" name="full_name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Phone</label>
                                <input type="tel" name="phone" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Email</label>
                                <input type="email" name="email" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Address Type</label>
                                <select name="address_type" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="both">Both Shipping & Billing</option>
                                    <option value="shipping">Shipping Only</option>
                                    <option value="billing">Billing Only</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Address Line 1</label>
                            <input type="text" name="address_line_1" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Address Line 2 (Optional)</label>
                            <input type="text" name="address_line_2" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">City</label>
                                <input type="text" name="city" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">State</label>
                                <input type="text" name="state" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Postal Code</label>
                                <input type="text" name="postal_code" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="is_default">
                                <span>Set as default address</span>
                            </label>
                        </div>
                        
                        <div style="margin-top: 30px; display: flex; gap: 15px;">
                            <button type="submit" class="btn" style="cursor: pointer;">Save Address</button>
                            <button type="button" class="btn btn-outline" onclick="toggleAddressForm()" style="cursor: pointer;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Saved Addresses List -->
                <?php if (count($addresses) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php foreach ($addresses as $addr): ?>
                            <div style="background: white; border: 2px solid #ddd; border-radius: 8px; padding: 20px; position: relative;">
                                <?php if ($addr['is_default']): ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: #D4AF37; color: black; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Default</div>
                                <?php endif; ?>
                                
                                <h4 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($addr['full_name']); ?></h4>
                                <p style="margin: 5px 0; font-size: 14px; color: #666;">
                                    <?php echo htmlspecialchars($addr['address_line_1']); ?><br>
                                    <?php if ($addr['address_line_2']): echo htmlspecialchars($addr['address_line_2']) . '<br>'; endif; ?>
                                    <?php echo htmlspecialchars($addr['city']) . ', ' . htmlspecialchars($addr['state']) . ' ' . htmlspecialchars($addr['postal_code']); ?>
                                </p>
                                <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
                                    📞 <?php echo htmlspecialchars($addr['phone']); ?>
                                </p>
                                <p style="margin: 5px 0; font-size: 12px; color: #999;">
                                    Type: <strong><?php echo ucfirst(str_replace('_', ' ', $addr['address_type'])); ?></strong>
                                </p>
                                
                                <div style="margin-top: 15px; display: flex; gap: 10px;">
                                    <a href="<?php echo BASE_URL; ?>user/edit-address?id=<?php echo $addr['id']; ?>" style="color: #D4AF37; text-decoration: none; font-size: 14px; font-weight: 600;">Edit</a>
                                    <a href="<?php echo BASE_URL; ?>user/delete-address?id=<?php echo $addr['id']; ?>" onclick="return confirm('Delete this address?')" style="color: #dc3545; text-decoration: none; font-size: 14px; font-weight: 600;">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 8px;">
                        <h3 style="color: #999;">No addresses saved</h3>
                        <p style="color: #999;">Add your first address to get started</p>
                    </div>
                <?php endif; ?>
                
            </section>
        </div>
    </div>
</main>

<script>
function toggleAddressForm() {
    const form = document.getElementById('address-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
