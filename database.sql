-- Equipment Rental Management System Database
-- CMM007 Intranet Systems Development
-- Run this script in phpMyAdmin to set up the database

CREATE DATABASE IF NOT EXISTS equipment_rental;
USE equipment_rental;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    max_rentals INT(11) NOT NULL DEFAULT 5,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Equipment table
CREATE TABLE IF NOT EXISTS equipment (
    equipment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    condition_status ENUM('New', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good',
    total_quantity INT(11) NOT NULL DEFAULT 1,
    available_quantity INT(11) NOT NULL DEFAULT 1,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Rentals table
CREATE TABLE IF NOT EXISTS rentals (
    rental_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    equipment_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    rental_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    due_date DATETIME NOT NULL,
    return_date DATETIME DEFAULT NULL,
    status ENUM('active', 'returned', 'overdue') NOT NULL DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (full_name, email, username, password, role, max_rentals) VALUES
('System Admin', 'admin@rental.com', 'admin', '$2y$12$lVECOsmYD4QKg7njmb42CuZ2Qua8iYVCbRxgE8otrsee1oO6DvVpu', 'admin', 99);

-- Insert sample users (password for all: password123)
INSERT INTO users (full_name, email, username, password, role, max_rentals) VALUES
('John Smith', 'john@rgu.ac.uk', 'john', '$2y$12$ENMSpJI5VUl103/4PJn/Xe3iVB/24OXNFiQ9wOpcDeNMtsmjbs8Gy', 'user', 5),
('Jane Doe', 'jane@rgu.ac.uk', 'jane', '$2y$12$ENMSpJI5VUl103/4PJn/Xe3iVB/24OXNFiQ9wOpcDeNMtsmjbs8Gy', 'user', 5);

-- Insert sample equipment
INSERT INTO equipment (name, category, serial_number, condition_status, total_quantity, available_quantity, description) VALUES
('Dell Laptop', 'Computing', 'DL-2024-001', 'New', 10, 10, 'Dell Latitude 5540 Laptop with 16GB RAM'),
('HP Monitor', 'Display', 'HP-MON-002', 'Good', 15, 15, '27 inch HP E27 G4 FHD Monitor'),
('Projector Epson', 'Presentation', 'EP-PRJ-003', 'Good', 5, 5, 'Epson EB-U05 Full HD Projector'),
('USB-C Hub', 'Accessories', 'USB-HUB-004', 'New', 20, 20, '7-in-1 USB-C Docking Station'),
('Wireless Mouse', 'Accessories', 'WM-LOG-005', 'New', 30, 30, 'Logitech M720 Triathlon Multi-Device'),
('Mechanical Keyboard', 'Accessories', 'KB-CRS-006', 'Good', 15, 15, 'Corsair K70 RGB Mechanical Keyboard'),
('Webcam HD', 'Communication', 'WC-LOG-007', 'New', 12, 12, 'Logitech C920 HD Pro Webcam'),
('Ethernet Cable 5m', 'Networking', 'EC-CAT6-008', 'New', 50, 50, 'Cat6 Ethernet Cable 5 metres'),
('Arduino Kit', 'Electronics', 'ARD-KIT-009', 'Good', 8, 8, 'Arduino Uno Starter Kit with sensors'),
('Raspberry Pi 4', 'Electronics', 'RPI4-010', 'New', 10, 10, 'Raspberry Pi 4 Model B 4GB Kit');
