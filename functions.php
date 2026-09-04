<?php
require_once 'config.php';

// ============================================
// USER AUTHENTICATION FUNCTIONS
// ============================================

// Register new user
function registerUser($full_name, $email, $password, $phone) {
    global $conn;
    
    // Sanitize inputs
    $full_name = mysqli_real_escape_string($conn, trim($full_name));
    $email = mysqli_real_escape_string($conn, trim(strtolower($email)));
    $phone = mysqli_real_escape_string($conn, trim($phone));
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Invalid email format"];
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        return ["success" => false, "message" => "Password must be at least 8 characters"];
    }
    
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return ["success" => false, "message" => "Password must contain uppercase, lowercase, and number"];
    }
    
    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Email already registered"];
    }
    mysqli_stmt_close($stmt);
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $insert_query = "INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $hashed_password, $phone);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return ["success" => true, "message" => "Registration successful"];
    } else {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Registration failed"];
    }
}

// Login user
function loginUser($email, $password) {
    global $conn;
    
    $email = mysqli_real_escape_string($conn, trim(strtolower($email)));
    
    $query = "SELECT id, full_name, email, password FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['full_name'];
            $_SESSION['user_email'] = $row['email'];
            mysqli_stmt_close($stmt);
            return ["success" => true, "message" => "Login successful"];
        }
    }
    
    mysqli_stmt_close($stmt);
    return ["success" => false, "message" => "Invalid email or password"];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get user info
function getUserInfo($user_id) {
    global $conn;
    
    $query = "SELECT id, full_name, email, phone, created_at FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $user;
}

// Update user profile
function updateUserProfile($user_id, $full_name, $phone, $new_password = null) {
    global $conn;
    
    $full_name = mysqli_real_escape_string($conn, trim($full_name));
    $phone = mysqli_real_escape_string($conn, trim($phone));
    
    if ($new_password) {
        if (strlen($new_password) < 8) {
            return ["success" => false, "message" => "Password must be at least 8 characters"];
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET full_name = ?, phone = ?, password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $full_name, $phone, $hashed_password, $user_id);
    } else {
        $query = "UPDATE users SET full_name = ?, phone = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $full_name, $phone, $user_id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['user_name'] = $full_name;
        mysqli_stmt_close($stmt);
        return ["success" => true, "message" => "Profile updated successfully"];
    } else {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Update failed"];
    }
}

// ============================================
// PRODUCT CRUD OPERATIONS
// ============================================

// INSERT - Add new product
function addProduct($user_id, $product_name, $category, $quantity, $price, $description) {
    global $conn;
    
    // Sanitize inputs
    $product_name = mysqli_real_escape_string($conn, trim($product_name));
    $category = mysqli_real_escape_string($conn, trim($category));
    $description = mysqli_real_escape_string($conn, trim($description));
    
    // Validate product name
    if (strlen($product_name) < 3 || strlen($product_name) > 100) {
        return ["success" => false, "message" => "Product name must be 3-100 characters"];
    }
    
    // Validate quantity
    if (!is_numeric($quantity) || $quantity < 0) {
        return ["success" => false, "message" => "Quantity must be a positive number"];
    }
    
    // Validate price
    if (!is_numeric($price) || $price < 0) {
        return ["success" => false, "message" => "Price must be a positive number"];
    }
    
    // Check for duplicate product name for this user
    $check_query = "SELECT id FROM products WHERE user_id = ? AND product_name = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $product_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Product with this name already exists"];
    }
    mysqli_stmt_close($stmt);
    
    // Insert product
    $query = "INSERT INTO products (user_id, product_name, category, quantity, price, description) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "issids", $user_id, $product_name, $category, $quantity, $price, $description);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return ["success" => true, "message" => "Product added successfully"];
    } else {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Failed to add product"];
    }
}

// SELECT - Get all products for a user
function getAllProducts($user_id, $search = '', $category_filter = '') {
    global $conn;
    
    $query = "SELECT * FROM products WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";
    
    if ($search != '') {
        $search = mysqli_real_escape_string($conn, $search);
        $query .= " AND product_name LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    }
    
    if ($category_filter != '' && $category_filter != 'All') {
        $query .= " AND category = ?";
        $params[] = $category_filter;
        $types .= "s";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $products;
}

// SELECT - Get single product
function getProduct($product_id, $user_id) {
    global $conn;
    
    $query = "SELECT * FROM products WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $product;
}

// UPDATE - Update product
function updateProduct($product_id, $user_id, $product_name, $category, $quantity, $price, $description) {
    global $conn;
    
    // Sanitize inputs
    $product_name = mysqli_real_escape_string($conn, trim($product_name));
    $category = mysqli_real_escape_string($conn, trim($category));
    $description = mysqli_real_escape_string($conn, trim($description));
    
    // Validate
    if (strlen($product_name) < 3 || strlen($product_name) > 100) {
        return ["success" => false, "message" => "Product name must be 3-100 characters"];
    }
    
    if (!is_numeric($quantity) || $quantity < 0) {
        return ["success" => false, "message" => "Quantity must be a positive number"];
    }
    
    if (!is_numeric($price) || $price < 0) {
        return ["success" => false, "message" => "Price must be a positive number"];
    }
    
    // Update product
    $query = "UPDATE products SET product_name = ?, category = ?, quantity = ?, price = ?, description = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssiisii", $product_name, $category, $quantity, $price, $description, $product_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return ["success" => true, "message" => "Product updated successfully"];
    } else {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Failed to update product"];
    }
}

// DELETE - Delete product
function deleteProduct($product_id, $user_id) {
    global $conn;
    
    $query = "DELETE FROM products WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return ["success" => true, "message" => "Product deleted successfully"];
    } else {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Failed to delete product"];
    }
}

// ============================================
// STATISTICS FUNCTIONS
// ============================================

// Get inventory statistics
function getInventoryStats($user_id) {
    global $conn;
    
    $stats = [
        'total_products' => 0,
        'low_stock_items' => 0,
        'total_value' => 0
    ];
    
    // Total products
    $query = "SELECT COUNT(*) as count FROM products WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $stats['total_products'] = $row['count'];
    mysqli_stmt_close($stmt);
    
    // Low stock items (quantity < 10)
    $query = "SELECT COUNT(*) as count FROM products WHERE user_id = ? AND quantity < 10";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $stats['low_stock_items'] = $row['count'];
    mysqli_stmt_close($stmt);
    
    // Total inventory value
    $query = "SELECT SUM(quantity * price) as total FROM products WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $stats['total_value'] = $row['total'] ? $row['total'] : 0;
    mysqli_stmt_close($stmt);
    
    return $stats;
}

// Sanitize output to prevent XSS
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>