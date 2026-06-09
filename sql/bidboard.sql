-- BidBoard Database Schema
-- Run this in phpMyAdmin or via MySQL CLI

CREATE DATABASE IF NOT EXISTS bidboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bidboard;

-- Admins table (seeded with one default admin)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,         -- bcrypt hash
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Clients table (freelancer's counterpart who posts tasks)
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,         -- bcrypt hash
    is_active TINYINT(1) DEFAULT 1,         -- admin can deactivate
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tasks table (posted by clients)
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    budget DECIMAL(10,2) NOT NULL,          -- max budget client is willing to pay
    deadline DATE NOT NULL,
    status ENUM('open','in_progress','completed') DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Bids table (submitted by freelancers, no account needed)
CREATE TABLE bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    freelancer_name VARCHAR(100) NOT NULL,
    freelancer_email VARCHAR(150) NOT NULL,
    proposed_price DECIMAL(10,2) NOT NULL,
    pitch TEXT NOT NULL,                    -- short cover letter / why me
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Default admin seed (password: admin123)
-- Hash generated with password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO admins (username, password) VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);

