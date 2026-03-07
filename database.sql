-- ==============================================
-- Pharmacy Inventory & Online Medicine Ordering
-- Database Setup Script
-- Compatible with MySQL 5.7+ and MySQL 8+
-- ==============================================

CREATE DATABASE IF NOT EXISTS pharmacy_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pharmacy_db;

-- -----------------------------------------------
-- Table: admin
-- Stores pharmacy staff / admin credentials
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL  -- stored as bcrypt hash
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account: username=admin, password=admin123
-- (Password hashed with PHP password_hash)
INSERT INTO admin (username, password) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Note: change password after first login!

-- -----------------------------------------------
-- Table: medicines
-- Core inventory table
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS medicines (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(200) NOT NULL,
    category       VARCHAR(100) NOT NULL,
    manufacturer   VARCHAR(200) NOT NULL,
    batch_number   VARCHAR(100) NOT NULL,
    expiry_date    DATE         NOT NULL,
    price          DECIMAL(10,2) NOT NULL,
    stock_quantity INT          NOT NULL DEFAULT 0,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample medicines data
INSERT INTO medicines (name, category, manufacturer, batch_number, expiry_date, price, stock_quantity) VALUES
    ('Paracetamol 500mg', 'Pain Relief', 'PharmaCo Ltd', 'PCM-2024-01', '2026-12-31', 5.50, 200),
    ('Amoxicillin 250mg', 'Antibiotics', 'MediGen Pharma', 'AMX-2024-02', '2025-08-15', 45.00, 80),
    ('Cetirizine 10mg', 'Antihistamine', 'HealthPlus Inc', 'CTZ-2024-03', '2027-03-20', 8.75, 150),
    ('Metformin 500mg', 'Diabetes', 'GlucoMed Corp', 'MFM-2024-04', '2026-06-30', 12.00, 50),
    ('Omeprazole 20mg', 'Gastric', 'DigestCare Labs', 'OMP-2024-05', '2025-11-10', 18.50, 30),
    ('Vitamin C 500mg', 'Vitamins', 'NutriPharm Ltd', 'VTC-2024-06', '2027-01-01', 6.00, 7),
    ('Aspirin 75mg', 'Pain Relief', 'CardioMed Ltd', 'ASP-2024-07', CURDATE() - INTERVAL 10 DAY, 4.00, 60),
    ('Ibuprofen 400mg', 'Pain Relief', 'PharmaCo Ltd', 'IBF-2024-08', '2026-09-25', 9.00, 0);

-- -----------------------------------------------
-- Table: orders
-- Customer order header
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    customer_name  VARCHAR(200) NOT NULL,
    phone          VARCHAR(20)  NOT NULL,
    address        TEXT         NOT NULL,
    payment_method ENUM('cash_on_delivery','pay_at_pharmacy') NOT NULL DEFAULT 'cash_on_delivery',
    order_date     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    status         ENUM('Pending','Ready for Pickup','Completed','Cancelled') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Table: order_items
-- Line-items for each order
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT            NOT NULL,
    medicine_id INT            NOT NULL,
    quantity    INT            NOT NULL,
    price       DECIMAL(10,2)  NOT NULL,  -- price locked at order time
    FOREIGN KEY (order_id)    REFERENCES orders(id)    ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
