<?php
/**
 * LUNIX WEAR - User Authentication Class
 */

class Auth {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Register new customer
     */
    public function register($name, $email, $password, $phone = '') {
        // Check if email exists
        $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $this->db->bind(1, $email, 's');
        $this->db->execute();
        
        if ($this->db->getResult()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Validate password length
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Hash password
        $hashedPassword = hashPassword($password);

        // Insert user
        $this->db->prepare('INSERT INTO users (name, email, password, phone, created_at) VALUES (?, ?, ?, ?, NOW())');
        $this->db->bind(1, $name, 's');
        $this->db->bind(2, $email, 's');
        $this->db->bind(3, $hashedPassword, 's');
        $this->db->bind(4, $phone, 's');
        
        if ($this->db->execute()) {
            $userId = $this->db->lastInsertId();
            logActivity($userId, 'User registered', 'New account created');
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $this->db->prepare('SELECT id, name, email, password, is_active FROM users WHERE email = ? LIMIT 1');
        $this->db->bind(1, $email, 's');
        $this->db->execute();
        
        $user = $this->db->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated'];
        }

        if (!verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = 'customer';

        // Update last login
        $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $this->db->bind(1, $user['id'], 'i');
        $this->db->execute();

        logActivity($user['id'], 'User login', 'Customer logged in');
        
        return ['success' => true, 'message' => 'Login successful'];
    }

    /**
     * Admin login
     */
    public function adminLogin($email, $password) {
        $this->db->prepare('SELECT id, name, email, password, role, is_active FROM admins WHERE email = ? LIMIT 1');
        $this->db->bind(1, $email, 's');
        $this->db->execute();
        
        $admin = $this->db->fetch();
        
        if (!$admin) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        if (!$admin['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated'];
        }

        if (!verifyPassword($password, $admin['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Set session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['role'] = $admin['role'];

        // Update last login
        $this->db->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?');
        $this->db->bind(1, $admin['id'], 'i');
        $this->db->execute();

        logActivity(null, 'Admin login', 'Admin: ' . $admin['name'] . ' logged in');
        
        return ['success' => true, 'message' => 'Login successful'];
    }

    /**
     * Logout
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            logActivity($userId, 'User logout', 'User logged out');
        }
        session_destroy();
    }

    /**
     * Send password reset email
     */
    public function sendResetEmail($email) {
        $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $this->db->bind(1, $email, 's');
        $this->db->execute();
        
        if ($this->db->getResult()->num_rows === 0) {
            // Don't reveal if email exists
            return ['success' => true, 'message' => 'If email exists, reset link will be sent'];
        }

        $user = $this->db->fetch();
        $token = generateToken();
        $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->db->prepare('UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?');
        $this->db->bind(1, $token, 's');
        $this->db->bind(2, $expiryTime, 's');
        $this->db->bind(3, $user['id'], 'i');
        $this->db->execute();

        $resetLink = BASE_URL . 'reset-password?token=' . $token;
        $message = "Click the link below to reset your password:\n" . $resetLink . "\n\nThis link will expire in 1 hour.";
        
        sendEmail($email, 'Password Reset Request', $message);
        logActivity($user['id'], 'Password reset requested', 'Reset email sent');

        return ['success' => true, 'message' => 'Password reset link sent to your email'];
    }
}

?>