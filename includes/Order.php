<?php
/**
 * LUNIX WEAR - Order Management Class
 */

class Order {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Create new order
     */
    public function createOrder($userId, $orderData) {
        $orderNumber = generateInvoiceNumber();
        
        $this->db->prepare(`
            INSERT INTO orders (
                order_number, user_id, subtotal, shipping_cost, tax, 
                discount, total, status, payment_method, shipping_address_id,
                billing_address_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, NOW())
        `);

        $this->db->bind(1, $orderNumber, 's');
        $this->db->bind(2, $userId, 'i');
        $this->db->bind(3, $orderData['subtotal'], 'd');
        $this->db->bind(4, $orderData['shipping_cost'], 'd');
        $this->db->bind(5, $orderData['tax'], 'd');
        $this->db->bind(6, $orderData['discount'], 'd');
        $this->db->bind(7, $orderData['total'], 'd');
        $this->db->bind(8, $orderData['payment_method'], 's');
        $this->db->bind(9, $orderData['shipping_address_id'], 'i');
        $this->db->bind(10, $orderData['billing_address_id'] ?? $orderData['shipping_address_id'], 'i');

        if ($this->db->execute()) {
            return ['success' => true, 'order_id' => $this->db->lastInsertId(), 'order_number' => $orderNumber];
        } else {
            return ['success' => false, 'message' => 'Failed to create order'];
        }
    }

    /**
     * Add items to order
     */
    public function addOrderItems($orderId, $items) {
        foreach ($items as $item) {
            $this->db->prepare(`
                INSERT INTO order_items (order_id, product_id, quantity, price, size, color, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            `);

            $this->db->bind(1, $orderId, 'i');
            $this->db->bind(2, $item['product_id'], 'i');
            $this->db->bind(3, $item['quantity'], 'i');
            $this->db->bind(4, $item['price'], 'd');
            $this->db->bind(5, $item['size'] ?? '', 's');
            $this->db->bind(6, $item['color'] ?? '', 's');
            
            $this->db->execute();
        }
        return true;
    }

    /**
     * Get user orders
     */
    public function getUserOrders($userId, $limit = 50, $offset = 0) {
        $this->db->prepare(`
            SELECT * FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        `);
        $this->db->bind(1, $userId, 'i');
        $this->db->bind(2, $limit, 'i');
        $this->db->bind(3, $offset, 'i');
        $this->db->execute();
        return $this->db->fetchAll();
    }

    /**
     * Get order details
     */
    public function getOrderDetails($orderId) {
        $this->db->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $this->db->bind(1, $orderId, 'i');
        $this->db->execute();
        $order = $this->db->fetch();

        if ($order) {
            // Get order items
            $this->db->prepare('SELECT * FROM order_items WHERE order_id = ?');
            $this->db->bind(1, $orderId, 'i');
            $this->db->execute();
            $order['items'] = $this->db->fetchAll();
        }

        return $order;
    }

    /**
     * Update order status (Admin)
     */
    public function updateOrderStatus($orderId, $status) {
        $validStatuses = ['pending', 'processing', 'printing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $this->db->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
        $this->db->bind(1, $status, 's');
        $this->db->bind(2, $orderId, 'i');

        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Order status updated'];
        } else {
            return ['success' => false, 'message' => 'Failed to update order status'];
        }
    }

    /**
     * Get all orders (Admin)
     */
    public function getAllOrders($filters = [], $limit = 50, $offset = 0) {
        $query = 'SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1';
        $types = '';
        $params = [];

        if (isset($filters['status'])) {
            $query .= ' AND o.status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }

        if (isset($filters['search'])) {
            $query .= ' AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $types .= 'sss';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $query .= ' ORDER BY o.created_at DESC LIMIT ? OFFSET ?';
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key + 1, $value);
        }
        
        $this->db->execute();
        return $this->db->fetchAll();
    }
}

?>