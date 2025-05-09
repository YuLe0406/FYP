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
    P_Status INT(1) DEFAULT 0,-- 1 = blocked, 0 = active
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

---------------------------------------------------------------------------------------------------------------- YULE改了以上的sql

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

INSERT INTO PRODUCT (C_ID, P_Name, P_Price, P_Picture) VALUES
(1, 'White Oversized T', 69.90, 'images/1front.png'),
(1, 'Black Oversized T', 89.90, 'images/2front.png'),
(1, 'Red Oversized T', 79.90, 'images/3front.png'),
(1, 'Clay Oversized T', 79.90, 'images/4front.png'),
(1, 'Butter Oversized T', 79.90, 'images/5front.png'),
(1, 'Grey Oversized T', 69.90, 'images/6front.png'),
(1, 'Orchid Oversized T', 79.90, 'images/7front.png'),
(2, 'White Hoodie', 169.90, 'images/1Front.jpeg'),
(2, 'Grey Hoodie', 169.90, 'images/2Front.jpeg'),
(2, 'Charcoal Hoodie', 169.90, 'images/3Front.jpeg'),
(2, 'Black Hoodie', 169.90, 'images/4Front.jpeg'),
(2, 'Red Hoodie', 169.90, 'images/5Front.jpeg'),
(2, 'Green Hoodie', 169.90, 'images/6Front.jpeg'),
(2, 'Navy Hoodie', 169.90, 'images/7Front.jpeg');


INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES

-- Product 1: White Oversized T
(1, 'images/1Front.png'),
(1, 'images/1Back.png'),
(1, 'images/1PersonFront.png'),
(1, 'images/1PersonBack.png'),
(1, 'images/1PersonCloseup.png'),

-- Product 2: Black Oversized T
(2, 'images/2Front.png'),
(2, 'images/2Back.png'),
(2, 'images/2PersonFront.png'),
(2, 'images/2PersonBack.png'),
(2, 'images/2PersonCloseup.png'),

-- Product 3: Red Oversized T
(3, 'images/3Front.png'),
(3, 'images/3Back.png'),
(3, 'images/3PersonFront.png'),
(3, 'images/3PersonBack.png'),
(3, 'images/3PersonCloseup.png'),

-- Product 4: Clay Oversized T
(4, 'images/4Front.png'),
(4, 'images/4Back.png'),
(4, 'images/4PersonFront.png'),
(4, 'images/4PersonBack.png'),
(4, 'images/4PersonCloseup.png'),

-- Product 5: Butter Oversized T
(5, 'images/5Front.png'),
(5, 'images/5Back.png'),
(5, 'images/5PersonFront.png'),
(5, 'images/5PersonBack.png'),
(5, 'images/5PersonCloseup.png'),

-- Product 6: Grey Oversized T
(6, 'images/6Front.png'),
(6, 'images/6Back.png'),
(6, 'images/6PersonFront.png'),
(6, 'images/6PersonBack.png'),
(6, 'images/6PersonCloseup.png'),

-- Product 7: Orchid Oversized T
(7, 'images/7Front.png'),
(7, 'images/7Back.png'),
(7, 'images/7PersonFront.png'),
(7, 'images/7PersonBack.png'),
(7, 'images/7PersonCloseup.png'),

-- Product 8: White Hoodie
(8, 'images/1Front.jpeg'),
(8, 'images/1Back.jpeg'),
(8, 'images/1PersonFront.jpeg'),
(8, 'images/1PersonBack.jpeg'),
(8, 'images/1PersonCloseup.jpeg'),

-- Product 9: Grey Hoodie
(9, 'images/2Front.jpeg'),
(9, 'images/2Back.jpeg'),
(9, 'images/2PersonFront.jpeg'),
(9, 'images/2PersonBack.jpeg'),
(9, 'images/2PersonCloseup.jpeg'),

-- Product 10: Charcoal Hoodie
(10, 'images/3Front.jpeg'),
(10, 'images/3Back.jpeg'),
(10, 'images/3PersonFront.jpeg'),
(10, 'images/3PersonBack.jpeg'),
(10, 'images/3PersonCloseup.jpeg'),

-- Product 11: Black Hoodie
(11, 'images/4Front.jpeg'),
(11, 'images/4Back.jpeg'),
(11, 'images/4PersonFront.jpeg'),
(11, 'images/4PersonBack.jpeg'),
(11, 'images/4PersonCloseup.jpeg'),

-- Product 12: Red Hoodie
(12, 'images/5Front.jpeg'),
(12, 'images/5Back.jpeg'),
(12, 'images/5PersonFront.jpeg'),
(12, 'images/5PersonBack.jpeg'),
(12, 'images/5PersonCloseup.jpeg'),

-- Product 13: Green Hoodie
(13, 'images/6Front.jpeg'),
(13, 'images/6Back.jpeg'),
(13, 'images/6PersonFront.jpeg'),
(13, 'images/6PersonBack.jpeg'),
(13, 'images/6PersonCloseup.jpeg'),

-- Product 14: Navy Hoodie
(14, 'images/7Front.jpeg'),
(14, 'images/7Back.jpeg'),
(14, 'images/7PersonFront.jpeg'),
(14, 'images/7PersonBack.jpeg'),
(14, 'images/7PersonCloseup.jpeg');



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
