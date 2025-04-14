-- Create the database
CREATE DATABASE IF NOT EXISTS user_system 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE user_system;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL COMMENT 'Hashed password using password_hash()',
    phone VARCHAR(15) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male','female','other') NOT NULL,
    security_question VARCHAR(255) NOT NULL COMMENT 'User-selected security question',
    security_answer VARCHAR(255) NOT NULL COMMENT 'Plain text answer (not hashed)',
    reset_token VARCHAR(64) DEFAULT NULL COMMENT 'Password reset token',
    reset_token_expiry DATETIME DEFAULT NULL COMMENT 'Token expiration datetime',
    account_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_user_email (email),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB;

-- Sample test user
INSERT INTO users (
    first_name, 
    last_name, 
    email, 
    password_hash, 
    phone, 
    date_of_birth, 
    gender, 
    security_question, 
    security_answer
) VALUES (
    'John',
    'Doe',
    'john.doe@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    '15551234567',
    '1985-05-15',
    'male',
    'What city were you born in?',
    'New York' -- Stored in plain text as requested
);