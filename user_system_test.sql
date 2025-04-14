-- 创建数据库（使用您指定的名称）
CREATE DATABASE IF NOT EXISTS user_system_test 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE user_system_test;

-- 用户表结构
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    dob DATE NOT NULL COMMENT '与register.php中的字段名一致',
    gender ENUM('male','female','other') NOT NULL,
    security_question VARCHAR(255) NOT NULL,
    security_answer VARCHAR(255) NOT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    account_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 索引
    INDEX idx_user_email (email),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB;

-- 测试用户数据
INSERT INTO users (
    first_name, 
    last_name, 
    email, 
    password_hash, 
    phone, 
    dob, 
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
    'New York'
);