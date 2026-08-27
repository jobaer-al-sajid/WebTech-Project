-- =========================================================
-- Dental Palace - Database Schema
-- Import this file in phpMyAdmin (XAMPP) or via:
--   mysql -u root -p < dental_palace.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS dental_palace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dental_palace;

-- ---------------------------------------------------------
-- users : admin, employee, customer all live in one table
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','employee','customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    designation VARCHAR(100) DEFAULT NULL,   -- used for employees (Dentist, Receptionist..)
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- services offered by the clinic
-- ---------------------------------------------------------
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- equipments used inside the clinic
-- ---------------------------------------------------------
CREATE TABLE equipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(80) DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    purchase_date DATE DEFAULT NULL,
    status ENUM('working','maintenance','damaged') NOT NULL DEFAULT 'working',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- appointments booked by customers, handled by employees
-- ---------------------------------------------------------
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    employee_id INT DEFAULT NULL,
    service_id INT DEFAULT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- billing / invoices
-- ---------------------------------------------------------
CREATE TABLE billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    appointment_id INT DEFAULT NULL,
    employee_id INT DEFAULT NULL,
    items TEXT NOT NULL,                 -- short description of services/items billed
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Default password for ALL seeded accounts below is:  123456
-- (hash generated with PHP password_hash, bcrypt)
INSERT INTO users (name, email, password, role, phone, address, designation) VALUES
('Dental Palace Admin', 'admin@dentalpalace.com', '$2b$10$o3TEcyqAJRQR7qqf7/QoQufeseTOoWl0pqWFbry9sFcj1dBmMCfmG', 'admin', '01700000000', 'Dhaka, Bangladesh', 'System Admin'),
('Dr. Rafiul Islam', 'employee@dentalpalace.com', '$2b$10$o3TEcyqAJRQR7qqf7/QoQufeseTOoWl0pqWFbry9sFcj1dBmMCfmG', 'employee', '01711111111', 'Dhaka, Bangladesh', 'Senior Dentist'),
('Sajid Hasan', 'customer@dentalpalace.com', '$2b$10$o3TEcyqAJRQR7qqf7/QoQufeseTOoWl0pqWFbry9sFcj1dBmMCfmG', 'customer', '01722222222', 'Mirpur, Dhaka', NULL);

INSERT INTO services (name, description, price) VALUES
('Dental Checkup', 'General oral examination and consultation', 500.00),
('Tooth Extraction', 'Removal of a damaged or decayed tooth', 1500.00),
('Root Canal Treatment', 'Treatment of infected tooth pulp', 6000.00),
('Teeth Whitening', 'Cosmetic whitening / bleaching treatment', 3500.00),
('Dental Filling', 'Cavity filling with composite material', 1200.00),
('Braces / Orthodontics', 'Teeth alignment treatment', 25000.00),
('Scaling & Polishing', 'Professional cleaning of teeth', 1000.00);

INSERT INTO equipments (name, category, quantity, price, purchase_date, status) VALUES
('Dental Chair Unit', 'Chair', 4, 250000.00, '2023-01-15', 'working'),
('Autoclave Sterilizer', 'Sterilization', 2, 85000.00, '2023-03-10', 'working'),
('Digital X-Ray Machine', 'Imaging', 1, 450000.00, '2022-11-05', 'working'),
('Ultrasonic Scaler', 'Cleaning', 3, 30000.00, '2023-06-20', 'working'),
('LED Curing Light', 'Restorative', 5, 12000.00, '2023-08-01', 'working'),
('Suction Unit', 'Support', 4, 40000.00, '2022-09-12', 'maintenance');
