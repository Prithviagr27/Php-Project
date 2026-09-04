-- Create Database
CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Sample User (password: Admin@123)
INSERT INTO users (full_name, email, password, phone) VALUES 
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890');

-- Insert Sample Products
INSERT INTO products (user_id, product_name, category, quantity, price, description) VALUES 
(1, 'Laptop Dell XPS 15', 'Electronics', 15, 1299.99, 'High performance laptop with 16GB RAM'),
(1, 'iPhone 14 Pro', 'Electronics', 8, 999.99, 'Latest Apple smartphone'),
(1, 'Office Chair', 'Furniture', 25, 199.99, 'Ergonomic office chair'),
(1, 'Desk Lamp', 'Electronics', 50, 29.99, 'LED desk lamp with adjustable brightness'),
(1, 'T-Shirt Cotton', 'Clothing', 100, 19.99, 'Premium cotton t-shirt'),
(1, 'Jeans Denim', 'Clothing', 60, 49.99, 'Classic blue jeans'),
(1, 'Organic Coffee', 'Food', 5, 12.99, 'Premium organic coffee beans - Low Stock!'),
(1, 'Wooden Bookshelf', 'Furniture', 12, 149.99, '5-tier wooden bookshelf');