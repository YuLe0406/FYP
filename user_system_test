-- 创建独立测试数据库（不会影响其他数据）
CREATE DATABASE IF NOT EXISTS user_system_test 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE user_system_test;

-- ----------------------------
-- 核心用户表（避免使用保留关键字）
-- ----------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL COMMENT 'BCrypt哈希',
    phone VARCHAR(15) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1 COMMENT '0=禁用,1=启用'
) ENGINE=InnoDB;

-- ----------------------------
-- 密码重置系统
-- ----------------------------
CREATE TABLE password_reset_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token CHAR(64) NOT NULL COMMENT 'SHA256生成',
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- 安全问答系统
-- ----------------------------
CREATE TABLE security_questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE user_security_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_hash VARCHAR(255) NOT NULL COMMENT 'BCrypt哈希',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES security_questions(question_id)
) ENGINE=InnoDB;

-- ----------------------------
-- 审计日志（增强安全性）
-- ----------------------------
CREATE TABLE user_audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL COMMENT '可为空表示未登录操作',
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    action_type ENUM('register','login','password_reset','logout') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- 索引优化
-- ----------------------------
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_tokens_expiry ON password_reset_tokens(expires_at);
CREATE INDEX idx_audit_user ON user_audit_logs(user_id);

-- ----------------------------
-- 测试数据（可选）
-- ----------------------------
INSERT INTO security_questions (question_text) VALUES
('您出生的城市是？'),
('您第一只宠物的名字是？'),
('您母亲的婚前姓氏是？');

INSERT INTO users 
(first_name, last_name, email, password_hash, phone)
VALUES (
    '张',
    '伟',
    'zhangwei@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- 密码=password
    '13800138000'
);

INSERT INTO user_security_answers 
(user_id, question_id, answer_hash)
VALUES (
    1, 
    1,
    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' -- 答案=北京
);