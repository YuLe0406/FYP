-- USER Table
CREATE TABLE USER (
    U_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_FName VARCHAR(255) NOT NULL,
    U_LName VARCHAR(255) NOT NULL,
    U_DOB DATE NOT NULL,
    U_Gender VARCHAR(6) NOT NULL,
    U_Email VARCHAR(255) NOT NULL UNIQUE,
    U_Password VARCHAR(255) NOT NULL,
    U_PNumber VARCHAR(11) NOT NULL,
    U_SecurityQuestion VARCHAR(255) NOT NULL,
    U_SecurityAnswer VARCHAR(255) NOT NULL,
    U_Status INT(1) DEFAULT 0  -- 1=blocked, 0=active
);

-- ADMIN Table
CREATE TABLE ADMIN (
    A_ID INT AUTO_INCREMENT PRIMARY KEY,
    A_Name VARCHAR(255) NOT NULL,
    A_Password VARCHAR(255) NOT NULL,
    A_Email VARCHAR(255) NOT NULL UNIQUE,
    A_CN VARCHAR(11) NOT NULL,
    A_Level INT(1) NOT NULL,  -- 1 for Superadmin, 0 for Admin
    A_Status INT(1) DEFAULT 0  -- 1 = blocked, 0 = active
);

-- CATEGORIES Table
CREATE TABLE CATEGORIES (
    C_ID INT AUTO_INCREMENT PRIMARY KEY,
    C_Name VARCHAR(255) NOT NULL
);

CREATE TABLE PRODUCT_COLOR (
    PC_ID INT AUTO_INCREMENT PRIMARY KEY,
    COLOR_NAME VARCHAR(50) NOT NULL,
    COLOR_HEX VARCHAR(7) NOT NULL,  -- Stores hex codes like #FF0000
    COLOR_IMAGE VARCHAR(255)       -- Optional path to color swatch image
);

-- PRODUCT Table
CREATE TABLE PRODUCT (
    P_ID INT AUTO_INCREMENT PRIMARY KEY,
    C_ID INT NOT NULL,
    P_Name VARCHAR(255) NOT NULL,
    P_Price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (C_ID) REFERENCES CATEGORIES(C_ID)
);

CREATE TABLE PRODUCT_IMAGES (
    PI_ID INT AUTO_INCREMENT PRIMARY KEY,
    P_ID INT NOT NULL,
    PRODUCT_IMAGE VARCHAR(255) NOT NULL,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID) ON DELETE CASCADE
);

-- Simplified PRODUCT_VARIANTS (without color table)
CREATE TABLE PRODUCT_VARIANTS (
    PV_ID INT AUTO_INCREMENT PRIMARY KEY,
    P_ID INT NOT NULL,
    PC_ID INT NOT NULL,
    P_Size VARCHAR(255) NOT NULL,
    P_Quantity INT NOT NULL,
    FOREIGN KEY (PC_ID) REFERENCES PRODUCT_COLOR(PC_ID) ON DELETE CASCADE,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID) ON DELETE CASCADE
);

-- ADDRESS Table
CREATE TABLE ADDRESS (
    AD_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    AD_Details TEXT NOT NULL,
    AD_City VARCHAR(255) NOT NULL,
    AD_State VARCHAR(255) NOT NULL,
    AD_ZipCode VARCHAR(10) NOT NULL,
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE
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

CREATE TABLE ORDER_STATUS (
    OS_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_Status VARCHAR(255) NOT NULL
);

CREATE TABLE ORDERS (
    O_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    AD_ID INT NOT NULL,
    OS_ID INT NOT NULL,  -- This was missing in your original schema
    O_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    O_TotalAmount DECIMAL(10,2) NOT NULL,
    O_DC DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE,
    FOREIGN KEY (AD_ID) REFERENCES ADDRESS(AD_ID),
    FOREIGN KEY (OS_ID) REFERENCES ORDER_STATUS(OS_ID)
);

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

CREATE TABLE PAYMENT (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_status VARCHAR(255) NOT NULL DEFAULT 'Pending',
    transaction_id VARCHAR(255) UNIQUE,
    payment_details JSON,
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID) ON DELETE CASCADE
);

CREATE TABLE DELIVERY_STATUS (
    DS_ID INT AUTO_INCREMENT PRIMARY KEY,
    D_Status VARCHAR(255) NOT NULL
);

CREATE TABLE DELIVERY (
    D_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    D_Carrier VARCHAR(100) NOT NULL,          -- e.g., "FedEx", "DHL"
    D_TrackingNumber VARCHAR(255),            -- Carrier's tracking ID
    D_StartDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    D_EstimatedDelivery DATE NOT NULL,        -- Expected delivery date
    D_ActualDelivery DATETIME NULL,           -- When actually delivered
    DS_ID INT NOT NULL,
    FOREIGN KEY (DS_ID) REFERENCES DELIVERY_STATUS(DS_ID),
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID) ON DELETE CASCADE
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
('Men Top'),
('Woman Top');

INSERT INTO PRODUCT (C_ID, P_Name, P_Price) VALUES
(1, 'White Oversized T', 69.90),
(1, 'Black Oversized T', 89.90),
(1, 'Red Oversized T', 79.90),
(1, 'Clay Oversized T', 79.90),
(1, 'Butter Oversized T', 79.90),
(1, 'Grey Oversized T', 69.90),
(1, 'Orchid Oversized T', 79.90),
(2, 'White Hoodie', 169.90),
(2, 'Grey Hoodie', 169.90),
(2, 'Charcoal Hoodie', 169.90),
(2, 'Black Hoodie', 169.90),
(2, 'Red Hoodie', 169.90),
(2, 'Green Hoodie', 169.90),
(2, 'Navy Hoodie', 169.90);

INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES
(1, 'images/1front.png'),
(2, 'images/2front.png'),
(3, 'images/3front.png'),
(4, 'images/4front.png'),
(5, 'images/5front.png'),
(6, 'images/6front.png'),
(7, 'images/7front.png'),
(8, 'images/1Front.jpeg'),
(9, 'images/2Front.jpeg'),
(10, 'images/3Front.jpeg'),
(11, 'images/4Front.jpeg'),
(12, 'images/5Front.jpeg'),
(13, 'images/6Front.jpeg'),
(14, 'images/7Front.jpeg');


INSERT INTO PRODUCT_COLOR (COLOR_NAME, COLOR_HEX) VALUES
('White', '#FFFFFF'),
('Black', '#000000'),
('Red', '#FF0000'),
('Clay', '#B66E41'),
('Butter', '#FEEFB3'),
('Grey', '#808080'),
('Orchid', '#DA70D6'),
('Charcoal', '#36454F'),
('Green', '#228B22'),
('Navy', '#000080');

INSERT INTO PRODUCT_VARIANTS (P_ID, PC_ID, P_Size, P_Quantity) VALUES
-- White Oversized T-Shirt (P_ID = 1)
(1, 1, 'S', 10),
(1, 1, 'M', 12),
(1, 1, 'L', 8),
(1, 1, 'XL', 6),

-- Black Oversized T-Shirt (P_ID = 2)
(2, 2, 'S', 10),
(2, 2, 'M', 12),
(2, 2, 'L', 8),
(2, 2, 'XL', 6),

-- Red Oversized T-Shirt (P_ID = 3)
(3, 3, 'S', 10),
(3, 3, 'M', 12),
(3, 3, 'L', 8),
(3, 3, 'XL', 6),

-- Clay Oversized T-Shirt (P_ID = 4)
(4, 4, 'S', 10),
(4, 4, 'M', 12),
(4, 4, 'L', 8),
(4, 4, 'XL', 6),

-- Butter Oversized T-Shirt (P_ID = 5)
(5, 5, 'S', 10),
(5, 5, 'M', 12),
(5, 5, 'L', 8),
(5, 5, 'XL', 6),

-- Grey Oversized T-Shirt (P_ID = 6)
(6, 6, 'S', 10),
(6, 6, 'M', 12),
(6, 6, 'L', 8),
(6, 6, 'XL', 6),

-- Orchid Oversized T-Shirt (P_ID = 7)
(7, 7, 'S', 10),
(7, 7, 'M', 12),
(7, 7, 'L', 8),
(7, 7, 'XL', 6),

-- White Hoodie (P_ID = 8)
(8, 1, 'S', 5),
(8, 1, 'M', 7),
(8, 1, 'L', 4),
(8, 1, 'XL', 3),

-- Grey Hoodie (P_ID = 9)
(9, 6, 'S', 5),
(9, 6, 'M', 7),
(9, 6, 'L', 4),
(9, 6, 'XL', 3),

-- Charcoal Hoodie (P_ID = 10)
(10, 8, 'S', 5),
(10, 8, 'M', 7),
(10, 8, 'L', 4),
(10, 8, 'XL', 3),

-- Black Hoodie (P_ID = 11)
(11, 2, 'S', 5),
(11, 2, 'M', 7),
(11, 2, 'L', 4),
(11, 2, 'XL', 3),

-- Red Hoodie (P_ID = 12)
(12, 3, 'S', 5),
(12, 3, 'M', 7),
(12, 3, 'L', 4),
(12, 3, 'XL', 3),

-- Green Hoodie (P_ID = 13)
(13, 9, 'S', 5),
(13, 9, 'M', 7),
(13, 9, 'L', 4),
(13, 9, 'XL', 3),

-- Navy Hoodie (P_ID = 14)
(14, 10, 'S', 5),
(14, 10, 'M', 7),
(14, 10, 'L', 4),
(14, 10, 'XL', 3);


