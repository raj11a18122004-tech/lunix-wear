<?php
/**
 * LUNIX WEAR - Product Management Class
 */

class Product {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Get all products with filters
     */
    public function getProducts($filters = []) {
        $query = 'SELECT * FROM products WHERE is_active = 1';
        $types = '';
        $params = [];

        if (isset($filters['category_id'])) {
            $query .= ' AND category_id = ?';
            $types .= 'i';
            $params[] = $filters['category_id'];
        }

        if (isset($filters['search'])) {
            $query .= ' AND (name LIKE ? OR description LIKE ?)';
            $types .= 'ss';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (isset($filters['min_price'])) {
            $query .= ' AND price >= ?';
            $types .= 'd';
            $params[] = $filters['min_price'];
        }

        if (isset($filters['max_price'])) {
            $query .= ' AND price <= ?';
            $types .= 'd';
            $params[] = $filters['max_price'];
        }

        if (isset($filters['featured']) && $filters['featured']) {
            $query .= ' AND is_featured = 1';
        }

        if (isset($filters['best_seller']) && $filters['best_seller']) {
            $query .= ' AND is_best_seller = 1';
        }

        if (isset($filters['new_arrival']) && $filters['new_arrival']) {
            $query .= ' AND is_new_arrival = 1';
        }

        $query .= ' ORDER BY created_at DESC';

        if (isset($filters['limit'])) {
            $query .= ' LIMIT ?';
            $types .= 'i';
            $params[] = $filters['limit'];
        }

        $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key + 1, $value);
        }
        
        $this->db->execute();
        return $this->db->fetchAll();
    }

    /**
     * Get product by ID
     */
    public function getProductById($id) {
        $this->db->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
        $this->db->bind(1, $id, 'i');
        $this->db->execute();
        $product = $this->db->fetch();
        
        if ($product) {
            // Get images
            $this->db->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC');
            $this->db->bind(1, $id, 'i');
            $this->db->execute();
            $product['images'] = $this->db->fetchAll();

            // Get variants
            $this->db->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY size, color');
            $this->db->bind(1, $id, 'i');
            $this->db->execute();
            $product['variants'] = $this->db->fetchAll();
        }
        
        return $product;
    }

    /**
     * Add product (Admin)
     */
    public function addProduct($data) {
        $slug = generateSlug($data['name']);
        
        // Check if SKU exists
        $this->db->prepare('SELECT id FROM products WHERE sku = ? LIMIT 1');
        $this->db->bind(1, $data['sku'], 's');
        $this->db->execute();
        
        if ($this->db->getResult()->num_rows > 0) {
            return ['success' => false, 'message' => 'SKU already exists'];
        }

        $highlights = json_encode($data['highlights'] ?? []);
        $specifications = json_encode($data['specifications'] ?? []);
        $dimensions = json_encode($data['dimensions'] ?? []);

        $this->db->prepare(`
            INSERT INTO products (
                category_id, name, sku, slug, brand, short_description, 
                full_description, highlights, specifications, material, 
                care_instructions, price, sale_price, cost_price, stock_quantity, 
                weight, dimensions, main_image, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        `);

        $this->db->bind(1, $data['category_id'], 'i');
        $this->db->bind(2, $data['name'], 's');
        $this->db->bind(3, $data['sku'], 's');
        $this->db->bind(4, $slug, 's');
        $this->db->bind(5, $data['brand'] ?? '', 's');
        $this->db->bind(6, $data['short_description'] ?? '', 's');
        $this->db->bind(7, $data['full_description'] ?? '', 's');
        $this->db->bind(8, $highlights, 's');
        $this->db->bind(9, $specifications, 's');
        $this->db->bind(10, $data['material'] ?? '', 's');
        $this->db->bind(11, $data['care_instructions'] ?? '', 's');
        $this->db->bind(12, $data['price'], 'd');
        $this->db->bind(13, $data['sale_price'] ?? null, 'd');
        $this->db->bind(14, $data['cost_price'] ?? null, 'd');
        $this->db->bind(15, $data['stock_quantity'] ?? 0, 'i');
        $this->db->bind(16, $data['weight'] ?? null, 'd');
        $this->db->bind(17, $dimensions, 's');
        $this->db->bind(18, $data['main_image'] ?? '', 's');

        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Product added successfully', 'product_id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Failed to add product'];
        }
    }

    /**
     * Update product (Admin)
     */
    public function updateProduct($id, $data) {
        $update = 'UPDATE products SET ';
        $fields = [];
        $types = '';
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $types .= 's';
            $params[] = $data['name'];
        }
        if (isset($data['price'])) {
            $fields[] = 'price = ?';
            $types .= 'd';
            $params[] = $data['price'];
        }
        if (isset($data['sale_price'])) {
            $fields[] = 'sale_price = ?';
            $types .= 'd';
            $params[] = $data['sale_price'];
        }
        if (isset($data['stock_quantity'])) {
            $fields[] = 'stock_quantity = ?';
            $types .= 'i';
            $params[] = $data['stock_quantity'];
        }
        if (isset($data['is_featured'])) {
            $fields[] = 'is_featured = ?';
            $types .= 'i';
            $params[] = $data['is_featured'];
        }
        if (isset($data['is_best_seller'])) {
            $fields[] = 'is_best_seller = ?';
            $types .= 'i';
            $params[] = $data['is_best_seller'];
        }
        if (isset($data['is_new_arrival'])) {
            $fields[] = 'is_new_arrival = ?';
            $types .= 'i';
            $params[] = $data['is_new_arrival'];
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $update .= implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?';
        $types .= 'i';
        $params[] = $id;

        $this->db->prepare($update);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key + 1, $value);
        }

        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Product updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update product'];
        }
    }

    /**
     * Delete product (Admin)
     */
    public function deleteProduct($id) {
        $this->db->prepare('DELETE FROM products WHERE id = ?');
        $this->db->bind(1, $id, 'i');
        
        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Product deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete product'];
        }
    }
}

?>