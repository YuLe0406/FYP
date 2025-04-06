-- USER Table
CREATE TABLE USER (
    U_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_FName VARCHAR(255) NOT NULL,
    U_LName VARCHAR(255) NOT NULL,
    U_DOB DATE NOT NULL,
    U_Gender ENUM('Male', 'Female', 'Other') NOT NULL,
    U_Email VARCHAR(255) NOT NULL UNIQUE,
    U_Password VARCHAR(255) NOT NULL,
    U_PNumber VARCHAR(11) NOT NULL,
    U_SecurityQuestion ENUM('Primary School', 'First Pet', 'Mother Maiden Name', 'Favorite Book') NOT NULL,
    U_SecurityAnswer VARCHAR(255) NOT NULL 
);

-- ADMIN Table
CREATE TABLE ADMIN (
    A_ID INT AUTO_INCREMENT PRIMARY KEY,
    A_Name VARCHAR(255) NOT NULL,
    A_Password VARCHAR(255) NOT NULL,
    A_Email VARCHAR(255) NOT NULL UNIQUE,
    A_CN VARCHAR(20) NOT NULL,
    A_Level TINYINT(1) NOT NULL  -- 1 for Superadmin, 0 for Admin
);

-- Simplified USER_STATUS for blocking users
CREATE TABLE USER_STATUS (
    US_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    US_Blocked TINYINT(1) DEFAULT 0,  -- 1=blocked, 0=active
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE
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
    P_Picture BLOB,
    FOREIGN KEY (C_ID) REFERENCES CATEGORIES(C_ID)
);

-- Simplified PRODUCT_VARIANTS (without color table)
CREATE TABLE PRODUCT_VARIANTS (
    PV_ID INT AUTO_INCREMENT PRIMARY KEY,
    P_ID INT NOT NULL,
    PC_ID INT NOT NULL,
    P_Size ENUM('XS','S','M','L','XL','XXL','XXXL') NOT NULL,
    P_Quantity INT NOT NULL,
    FOREIGN KEY (P_ID) REFERENCES PRODUCT(P_ID) ON DELETE CASCADE
);

CREATE TABLE PRODUCT_COLOR (
    PC_ID INT AUTO_INCREMENT PRIMARY KEY,
    COLOR_NAME VARCHAR(50) NOT NULL,
    COLOR_HEX VARCHAR(7) NOT NULL,  -- Stores hex codes like #FF0000
    COLOR_IMAGE VARCHAR(255)       -- Optional path to color swatch image
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

-- Simplified ORDERS with only 3 statuses
CREATE TABLE ORDERS (
    O_ID INT AUTO_INCREMENT PRIMARY KEY,
    U_ID INT NOT NULL,
    AD_ID INT NOT NULL,
    O_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    O_TotalAmount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (U_ID) REFERENCES USER(U_ID) ON DELETE CASCADE,
    FOREIGN KEY (AD_ID) REFERENCES ADDRESS(AD_ID)
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
    payment_method ENUM('Credit Card', 'Debit Card', 'Cash on Delivery', 'E-Wallet') NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
    transaction_id VARCHAR(255) UNIQUE,
    payment_details JSON,
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID),
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID) ON DELETE CASCADE
);

CREATE TABLE DELIVERY (
    D_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    D_Carrier VARCHAR(100) NOT NULL,          -- e.g., "FedEx", "DHL"
    D_TrackingNumber VARCHAR(255),            -- Carrier's tracking ID
    D_StartDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    D_EstimatedDelivery DATE NOT NULL,        -- Expected delivery date
    D_ActualDelivery DATETIME NULL,           -- When actually delivered
    D_Status ENUM('Preparing', 'In Transit', 'Out for Delivery', 
                 'Delivered', 'Failed Attempt', 'Returned') NOT NULL DEFAULT 'Preparing',
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID) ON DELETE CASCADE
);

-- Simplified BANNER (text only)
CREATE TABLE BANNER (
    B_ID INT AUTO_INCREMENT PRIMARY KEY,
    B_Text TEXT NOT NULL,
    B_Status TINYINT(1) DEFAULT 1  -- 1=active, 0=inactive
);

CREATE TABLE VOUCHER (
    V_ID INT AUTO_INCREMENT PRIMARY KEY,
    V_Code VARCHAR(50) NOT NULL UNIQUE,
    V_Discount DECIMAL(5,2) NOT NULL CHECK (V_Discount > 0 AND V_Discount <= 100),
    V_ExpiryDate DATE NOT NULL,
    V_UsageLimit INT NOT NULL DEFAULT 1,
    V_UsedCount INT DEFAULT 0,
    V_Status TINYINT(1) DEFAULT 1  -- 1=active, 0=inactive
);

-- Junction table for voucher usage tracking
CREATE TABLE ORDER_VOUCHER (
    OV_ID INT AUTO_INCREMENT PRIMARY KEY,
    O_ID INT NOT NULL,
    V_ID INT NOT NULL,
    FOREIGN KEY (O_ID) REFERENCES ORDERS(O_ID),
    FOREIGN KEY (V_ID) REFERENCES VOUCHER(V_ID)
);

 --TESTING FOR ORDER

SELECT 
    O.O_ID AS OrderID,
    U.U_FName AS CustomerName,
    O.O_Date AS OrderDate,
    O.O_TotalAmount AS TotalAmount,
    P.P_Name AS ProductName,
    OI.OI_Quantity AS Quantity,
    OI.OI_Price AS Price
FROM 
    ORDERS O
JOIN 
    USER U ON O.U_ID = U.U_ID
JOIN 
    ORDER_ITEMS OI ON O.O_ID = OI.O_ID
JOIN 
    PRODUCT P ON OI.P_ID = P.P_ID
ORDER BY 
    O.O_Date DESC;


--TESTING FOR REPORT

SELECT 
    O.O_ID AS OrderID,
    U.U_FName AS CustomerName,
    O.O_Date AS OrderDate,
    O.O_TotalAmount AS TotalAmount,
    P.P_Name AS ProductName,
    OI.OI_Quantity AS Quantity,
    OI.OI_Price AS Price,
    O.O_Status AS Status
FROM 
    ORDERS O
JOIN 
    USER U ON O.U_ID = U.U_ID
JOIN 
    ORDER_ITEMS OI ON O.O_ID = OI.O_ID
JOIN 
    PRODUCT P ON OI.P_ID = P.P_ID
WHERE 
    O.O_Date BETWEEN '2023-10-01' AND '2023-10-31' -- Example date range
    AND P.C_ID = 1 -- Example category filter
ORDER BY 
    O.O_Date DESC;

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


INSERT INTO PRODUCT_VARIANTS (P_ID, P_Color, P_Size, P_Quantity) VALUES
-- White Oversized T-Shirt
(1, 'White', 'S', 10),
(1, 'White', 'M', 12),
(1, 'White', 'L', 8),
(1, 'White', 'XL', 6),

-- Black Oversized T-Shirt
(2, 'Black', 'S', 10),
(2, 'Black', 'M', 12),
(2, 'Black', 'L', 8),
(2, 'Black', 'XL', 6),

-- Red Oversized T-Shirt
(3, 'Red', 'S', 10),
(3, 'Red', 'M', 12),
(3, 'Red', 'L', 8),
(3, 'Red', 'XL', 6),

-- Clay Oversized T-Shirt
(4, 'Clay', 'S', 10),
(4, 'Clay', 'M', 12),
(4, 'Clay', 'L', 8),
(4, 'Clay', 'XL', 6),

-- Butter Oversized T-Shirt
(5, 'Butter', 'S', 10),
(5, 'Butter', 'M', 12),
(5, 'Butter', 'L', 8),
(5, 'Butter', 'XL', 6),

-- Grey Oversized T-Shirt
(6, 'Grey', 'S', 10),
(6, 'Grey', 'M', 12),
(6, 'Grey', 'L', 8),
(6, 'Grey', 'XL', 6),

-- Orchid Oversized T-Shirt
(7, 'Orchid', 'S', 10),
(7, 'Orchid', 'M', 12),
(7, 'Orchid', 'L', 8),
(7, 'Orchid', 'XL', 6),

-- White Hoodie
(8, 'White', 'S', 5),
(8, 'White', 'M', 7),
(8, 'White', 'L', 4),
(8, 'White', 'XL', 3),

-- Grey Hoodie
(9, 'Grey', 'S', 5),
(9, 'Grey', 'M', 7),
(9, 'Grey', 'L', 4),
(9, 'Grey', 'XL', 3),

-- Charcoal Hoodie
(10, 'Charcoal', 'S', 5),
(10, 'Charcoal', 'M', 7),
(10, 'Charcoal', 'L', 4),
(10, 'Charcoal', 'XL', 3),

-- Black Hoodie
(11, 'Black', 'S', 5),
(11, 'Black', 'M', 7),
(11, 'Black', 'L', 4),
(11, 'Black', 'XL', 3),

-- Red Hoodie
(12, 'Red', 'S', 5),
(12, 'Red', 'M', 7),
(12, 'Red', 'L', 4),
(12, 'Red', 'XL', 3),

-- Green Hoodie
(13, 'Green', 'S', 5),
(13, 'Green', 'M', 7),
(13, 'Green', 'L', 4),
(13, 'Green', 'XL', 3),

-- Navy Hoodie
(14, 'Navy', 'S', 5),
(14, 'Navy', 'M', 7),
(14, 'Navy', 'L', 4),
(14, 'Navy', 'XL', 3);
