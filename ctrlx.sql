-- USER Table
CREATE TABLE USER (
    U_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_FName VARCHAR(50) NOT NULL,
    U_LName VARCHAR(50) NOT NULL,
    U_Email VARCHAR(100) NOT NULL UNIQUE,
    U_Password VARCHAR(255) NOT NULL,
    U_PNumber VARCHAR(15) NOT NULL,
    U_DOB DATE NOT NULL,
    U_Gender ENUM('male','female','other') NOT NULL,
    U_SecurityQuestion VARCHAR(255) NOT NULL,
    U_SecurityAnswer VARCHAR(255) NOT NULL,
    U_ResetToken VARCHAR(64) DEFAULT NULL,
    U_ResetTokenExpiry DATETIME DEFAULT NULL,
    U_AccountCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    U_LastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_email (U_Email),
    INDEX idx_reset_token (U_ResetToken)
);

-- USER ADDRESS Table
CREATE TABLE USER_ADDRESS (
    UA_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    UA_Type ENUM('home', 'work', 'other') NOT NULL DEFAULT 'home',
    UA_Address1 VARCHAR(255) NOT NULL,
    UA_Address2 VARCHAR(255),
    UA_Postcode VARCHAR(10) NOT NULL,
    UA_City VARCHAR(100) NOT NULL,
    UA_State ENUM(
        'Johor', 'Kedah', 'Kelantan', 'Melaka', 
        'Negeri Sembilan', 'Pahang', 'Penang', 'Perak', 'Perlis', 
        'Sabah', 'Sarawak', 'Selangor', 'Terengganu'
    ) NOT NULL,
    UA_IsDefault BOOLEAN NOT NULL DEFAULT FALSE,
    UA_Created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UA_Updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE,
    INDEX idx_user_address (U_ID)
);


-- CATEGORIES Table
CREATE TABLE CATEGORIES (
    C_ID INT AUTO_INCREMENT PRIMARY KEY,
    C_Name VARCHAR(255) NOT NULL
);

-- PRODUCT Table
CREATE TABLE PRODUCT (
    P_ID INT AUTO_INCREMENT PRIMARY KEY,
    C_ID INT NOT NULL,
    P_Name VARCHAR(255) NOT NULL,
    P_Price DECIMAL(10,2) NOT NULL,
    P_Picture VARCHAR(255) DEFAULT NULL,
    P_DES TEXT NOT NULL,
    P_Status INT(1) DEFAULT 0,-- 1 = blocked, 0 = active,
    FOREIGN KEY (C_ID) REFERENCES CATEGORIES(C_ID)
);

CREATE TABLE PRODUCT_IMAGES (
    PI_ID INT AUTO_INCREMENT PRIMARY KEY,
    P_ID INT NOT NULL,
    PRODUCT_IMAGE VARCHAR(255) NOT NULL,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID) ON DELETE CASCADE
);


-- Simplified PRODUCT_VARIANTS
CREATE TABLE PRODUCT_VARIANTS (
    PV_ID INT AUTO_INCREMENT PRIMARY KEY,
    P_ID INT NOT NULL,
    P_Size VARCHAR(50) NOT NULL,
    P_Quantity INT NOT NULL,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID) ON DELETE CASCADE
);

CREATE TABLE ORDERS (
    O_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    UA_ID INT NULL,
    OS_ID INT NOT NULL,
    O_TotalAmount DECIMAL(10,2) NOT NULL,
    O_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    O_Status ENUM('Processing','Shipped','Delivered') NOT NULL DEFAULT 'Processing', --------------- new line (yule need to update)
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE,
    FOREIGN KEY (UA_ID) REFERENCES USER_ADDRESS(UA_ID)
);

CREATE TABLE PAYMENT (
    Pay_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    Pay_Method ENUM('Credit Card', 'PayPal') DEFAULT 'Credit Card',
    Pay_Amount DECIMAL(10,2) NOT NULL,
    Pay_CardNumber VARCHAR(20),
    Pay_ExpiryDate VARCHAR(7),
    Pay_CVV VARCHAR(4),
    Pay_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ORDER_ITEMS Table
CREATE TABLE ORDER_ITEMS (
    OI_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    P_ID INT NOT NULL,
    PV_ID INT NULL,
    OI_Quantity INT NOT NULL,
    OI_Price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID) ON DELETE CASCADE,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID),
    FOREIGN KEY (PV_ID) REFERENCES PRODUCT_VARIANTS(PV_ID) ON DELETE SET NULL
);

-- YULE改了以上的sql

-- ADMIN Table
CREATE TABLE ADMIN (
    A_ID INT AUTO_INCREMENT PRIMARY KEY,
    A_Name VARCHAR(255) NOT NULL,
    A_Password VARCHAR(255) NOT NULL,
    A_Email VARCHAR(255) NOT NULL UNIQUE,
    A_CN VARCHAR(11) NOT NULL,
    A_Picture VARCHAR(255) NULL,
    A_Level INT(1) NOT NULL,  -- 1 for Superadmin, 0 for Admin
    A_Status INT(1) DEFAULT 0  -- 1 = blocked, 0 = active
);

-- Simplified WISHLIST
CREATE TABLE WISHLIST (
    W_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    P_ID INT NOT NULL,
    W_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID) ON DELETE CASCADE,
    UNIQUE (U_ID, P_ID)
);

-- CART Table
CREATE TABLE CART (
    CART_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    P_ID INT NOT NULL,
    CART_Quantity INT NOT NULL,
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID) ON DELETE CASCADE
);

CREATE TABLE DELIVERY (
    D_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    DC_ID INT NOT NULL,
    D_TrackingNumber VARCHAR(255),            -- Carrier's tracking ID
    D_StartDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    D_EstimatedDelivery DATE NOT NULL,        -- Expected delivery date
    D_ActualDelivery DATETIME NULL,           -- When actually delivered
    D_Status ENUM('Preparing','Shipped','Delivered') NOT NULL DEFAULT 'Preparing',
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID) ON DELETE CASCADE
);

CREATE TABLE DELIVERY_CARRIER (
    DC_ID INT AUTO_INCREMENT PRIMARY KEY,
    DC_Name VARCHAR(100) NOT NULL UNIQUE     -- e.g., "FedEx", "DHL", "UPS"
);

-- Simplified BANNER
CREATE TABLE BANNER (
    B_ID INT AUTO_INCREMENT PRIMARY KEY,
    B_Text TEXT NOT NULL,
    B_Picture VARCHAR(255) NOT NULL,
    B_Status INT(1) DEFAULT 1  -- 1=active, 0=inactive
);

CREATE TABLE BANNER_PICTURE (
    BP_ID INT AUTO_INCREMENT PRIMARY KEY,
    B_ID INT NOT NULL,
    B_Picture VARCHAR(255) NOT NULL,
    BP_Status INT(1) DEFAULT 1,  -- 1=active, 0=inactive
    FOREIGN KEY (B_ID) REFERENCES BANNER(B_ID) ON DELETE CASCADE
);

CREATE TABLE VOUCHER (
    V_ID INT AUTO_INCREMENT PRIMARY KEY,
    V_Code VARCHAR(50) NOT NULL UNIQUE,
    V_Discount DECIMAL(5,2) NOT NULL CHECK (V_Discount > 0 AND V_Discount <= 100),
    V_ExpiryDate DATE NOT NULL,
    V_UsageLimit INT NOT NULL DEFAULT 1,
    V_UsedCount INT DEFAULT 0,
    V_Status INT(1) DEFAULT 1  -- 1=active, 0=inactive
);

-- Junction table for voucher usage tracking
CREATE TABLE ORDER_VOUCHER (
    OV_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    V_ID INT NOT NULL,
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID),
    FOREIGN KEY (V_ID) REFERENCES VOUCHER(V_ID)
);

CREATE TABLE FEEDBACK (
    F_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    F_Type VARCHAR(100) NOT NULL,
    F_Description TEXT NOT NULL,
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID)
);

CREATE TABLE REPLY_FEEDBACK (
    RF_ID INT AUTO_INCREMENT PRIMARY KEY,
    A_ID INT NOT NULL,
    F_ID INT NOT NULL,
    RF_Reply TEXT NOT NULL,
    FOREIGN KEY (A_ID) REFERENCES ADMIN(A_ID),
    FOREIGN KEY (F_ID) REFERENCES FEEDBACK(F_ID)
);

INSERT INTO ADMIN (A_Name, A_Password, A_Email, A_CN, A_Level, A_Status) VALUES
('WEIFU','weifu123','weifu@gmail.com','01234567890',1,0),
('YULE','yule123','yule@gmail.com','0123456789',1,0),
('SHIHAO','shihao123','shihao@gmail.com','01234567891',0,0);

INSERT INTO CATEGORIES (C_NAME) VALUES
('Oversized T'),
('Hoodies');

-- Men's T-Shirts
INSERT INTO PRODUCT (C_ID, P_Name, P_Price) VALUES
(1, 'Oversized White T-Shirt', 69.90),
(1, 'Oversized Black T-Shirt', 89.90),
(1, 'Oversized Red T-Shirt', 79.90),
(1, 'Oversized Clay T-Shirt', 79.90),
(1, 'Oversized Butter T-Shirt', 79.90),
(1, 'Oversized Grey T-Shirt', 69.90),
(1, 'Oversized Orchid T-Shirt', 79.90),

-- Women's Hoodies
(2, 'White Hoodie', 169.90),
(2, 'Grey Hoodie', 169.90),
(2, 'Charcoal Hoodie', 169.90),
(2, 'Black Hoodie', 169.90),
(2, 'Red Hoodie', 169.90),
(2, 'Green Hoodie', 169.90),
(2, 'Navy Hoodie', 169.90);

-- T-SHIRTS

-- P_ID 1: Oversized White T-Shirt
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(1, 'FYP/images/1Person Back.png'),
(1, 'FYP/images/1Person Closeup.png'),
(1, 'FYP/images/1Person Front.png'),
(1, 'FYP/images/1Front.png'),
(1, 'FYP/images/1Back.png');

-- P_ID 2: Oversized Black T-Shirt
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(2, 'FYP/images/2Person Back.png'),
(2, 'FYP/images/2Person Closeup.png'),
(2, 'FYP/images/2Person Front.png'),
(2, 'FYP/images/2Front.png'),
(2, 'FYP/images/2Back.png');

-- P_ID 3: Oversized Red T-Shirt
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(3, 'FYP/images/3Person Back.png'),
(3, 'FYP/images/3Person Closeup.png'),
(3, 'FYP/images/3Person Front.png'),
(3, 'FYP/images/3Front.png'),
(3, 'FYP/images/3Back.png');

-- P_ID 4: Oversized Clay T-Shirt
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(4, 'FYP/images/4Person Back.png'),
(4, 'FYP/images/4Person Closeup.png'),
(4, 'FYP/images/4Person Front.png'),
(4, 'FYP/images/4Front.png'),
(4, 'FYP/images/4Back.png');

-- P_ID 5: Oversized Butter T-Shirt
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(5, 'FYP/images/5Back.png'),
(5, 'FYP/images/5Front.png'),
(5, 'FYP/images/5Person Back.png'),
(5, 'FYP/images/5Person Closeup.png'),
(5, 'FYP/images/5Person Front.png');

-- P_ID 6: Oversized Grey T-Shirt
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(6, 'FYP/images/6Person Back.png'),
(6, 'FYP/images/6Person Closeup.png'),
(6, 'FYP/images/6Person Front.png'),
(6, 'FYP/images/6Front.png'),
(6, 'FYP/images/6Back.png');

-- P_ID 7: Oversized Orchid T-Shirt
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(7, 'FYP/images/7Person Back.png'),
(7, 'FYP/images/7Person Closeup.png'),
(7, 'FYP/images/7Person Front.png'),
(7, 'FYP/images/7Front.png'),
(7, 'FYP/images/7Back.png');

-- P_ID 8: White Hoodie
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(8, 'FYP/images/1Front.jpeg'),
(8, 'FYP/images/1Back.jpeg');

-- P_ID 9: Grey Hoodie
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(9, 'FYP/images/2Front.jpeg'),
(9, 'FYP/images/2Back.jpeg');

-- P_ID 10: Charcoal Hoodie
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(10, 'FYP/images/3Front.jpeg'),
(10, 'FYP/images/3Back.jpeg');

-- P_ID 11: Black Hoodie
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(11, 'FYP/images/4Front.jpeg'),
(11, 'FYP/images/4Back.jpeg');

-- P_ID 12: Red Hoodie
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(12, 'FYP/images/3Front.jpeg'),
(12, 'FYP/images/3Back.jpeg');

-- P_ID 13: Green Hoodie
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(13, 'FYP/images/6Front.jpeg'),
(13, 'FYP/images/6Back.jpeg');

-- P_ID 14: Navy Hoodie
INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(14, 'FYP/images/7Front.jpeg'),
(14, 'FYP/images/7Back.jpeg');


-- Men's T-Shirt Variants
INSERT INTO PRODUCT_VARIANTS (P_ID, P_Size, P_Quantity) VALUES
-- White T-Shirt (P_ID=1)
(1, 'S', 10), (1, 'M', 12), (1, 'L', 8), (1, 'XL', 6),

-- Black T-Shirt (P_ID=2)
(2, 'S', 10), (2, 'M', 12), (2, 'L', 8), (2, 'XL', 6),

-- Red T-Shirt (P_ID=3)
(3, 'S', 10), (3, 'M', 12), (3, 'L', 8), (3, 'XL', 6),

-- Clay T-Shirt (P_ID=4)
(4, 'S', 10), (4, 'M', 12), (4, 'L', 8), (4, 'XL', 6),

-- Butter T-Shirt (P_ID=5)
(5, 'S', 10), (5, 'M', 12), (5, 'L', 8), (5, 'XL', 6),

-- Grey T-Shirt (P_ID=6)
(6, 'S', 10), (6, 'M', 12), (6, 'L', 8), (6, 'XL', 6),

-- Orchid T-Shirt (P_ID=7)
(7, 'S', 10), (7, 'M', 12), (7, 'L', 8), (7, 'XL', 6),

-- White Hoodie (P_ID=8)
(8, 'S', 5), (8, 'M', 7), (8, 'L', 4), (8, 'XL', 3),

-- Grey Hoodie (P_ID=9)
(9, 'S', 5), (9, 'M', 7), (9, 'L', 4), (9, 'XL', 3),

-- Charcoal Hoodie (P_ID=10)
(10, 'S', 5), (10, 'M', 7), (10, 'L', 4), (10, 'XL', 3),

-- Black Hoodie (P_ID=11)
(11, 'S', 5), (11, 'M', 7), (11, 'L', 4), (11, 'XL', 3),

-- Red Hoodie (P_ID=12)
(12, 'S', 5), (12, 'M', 7), (12, 'L', 4), (12, 'XL', 3),

-- Green Hoodie (P_ID=13)
(13, 'S', 5), (13, 'M', 7), (13, 'L', 4), (13, 'XL', 3),

-- Navy Hoodie (P_ID=14)
(14, 'S', 5), (14, 'M', 7), (14, 'L', 4), (14, 'XL', 3);

INSERT INTO DELIVERY_CARRIER (DC_Name) VALUES 
('FedEx'),
('DHL'),
('Ninja Van');

-- Insert some vouchers
INSERT INTO VOUCHER (V_Code, V_Discount, V_ExpiryDate, V_UsageLimit) VALUES
('WELCOME10', 10.00, '2025-12-31', 100),
('SUMMER20', 20.00, '2025-09-30', 50),
('FREESHIP', 15.00, '2024-10-15', 200),
('NEWUSER25', 25.00, '2024-11-30', 75);
