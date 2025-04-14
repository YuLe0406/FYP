-- Create database
CREATE DATABASE IF NOT EXISTS user_system_test 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE user_system_test;

-- Core user table
CREATE TABLE users (
   CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('male','female','other') NOT NULL
);
) ENGINE=InnoDB;

-- Indexes
CREATE INDEX idx_users_email ON users(email);