-- Drop tables if they exist to start fresh
DROP TABLE IF EXISTS transaction_details;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS expenses;


-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'karyawan') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    category_id INT NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    pay_amount DECIMAL(10, 2) NOT NULL,
    change_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transaction Details table
CREATE TABLE transaction_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    product_id INT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Expenses table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    date DATE NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Seed Users (passwords are 'admin123' and 'karyawan123')
INSERT INTO users (username, password, name, role) VALUES 
('admin', '$2y$10$hxGlRuJrCkvezR/bq0XL..lRozwgb9JAdp1bI2zSCbkWrEdkwNN0W', 'Pemilik Toko (Admin)', 'admin'),
('karyawan', '$2y$10$xE3ACTIWjZZph1G/.N.nQ.uZA/P8/JkdBeAp9pN4Eod4.NGS8XDBS', 'Kasir Toko (Karyawan)', 'karyawan');

-- Seed Categories
INSERT INTO categories (name) VALUES 
('Makanan Ringan'),
('Minuman'),
('Kebutuhan Dapur'),
('Sabun & Detergen'),
('Alat Tulis');

-- Seed Products
INSERT INTO products (code, category_id, name, price, cost_price, stock, min_stock) VALUES 
('PRD001', 1, 'Chiki Taro Net 65g', 5500.00, 4200.00, 25, 5),
('PRD002', 1, 'Kripik Singkong Kusuka 180g', 12000.00, 9500.00, 15, 5),
('PRD003', 2, 'Aqua Botol 600ml', 3500.00, 2200.00, 50, 10),
('PRD004', 2, 'Teh Botol Sosro 450ml', 5000.00, 3800.00, 3, 5), -- Low stock product
('PRD005', 3, 'Minyak Goreng Bimoli 1L', 18500.00, 16000.00, 12, 4),
('PRD006', 3, 'Gula Pasir Gulaku 1kg', 16000.00, 14200.00, 2, 5), -- Low stock product
('PRD007', 4, 'Sabun Cair Biore 450ml Refill', 22000.00, 18500.00, 8, 3),
('PRD008', 4, 'Detergen Rinso Cair 750ml', 19500.00, 16800.00, 10, 3);
