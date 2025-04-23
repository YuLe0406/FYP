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
    C_Name VARCHAR(255) NOT NULL,
    C_Status INT(1) DEFAULT 0 -- 1 = blocked, 0 = active
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
    P_Status INT(1) DEFAULT 0, -- 1 = blocked, 0 = active
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

INSERT INTO CATEGORIES (C_NAME, C_Status) VALUES
('Men Top',0),
('Woman Top',0);

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
(1, 'FYP/images/1Front.png'),
(1, 'FYP/images/1Back.png'),
(2, 'FYP/images/2Front.png'),
(2, 'FYP/images/2Back.png'),
(3, 'FYP/images/3Front.png'),
(3, 'FYP/images/3Back.png'),
(4, 'FYP/images/4Front.png'),
(4, 'FYP/images/4Back.png'),
(5, 'FYP/images/5Front.png'),
(5, 'FYP/images/5Back.png'),
(6, 'FYP/images/6Front.png'),
(6, 'FYP/images/6Back.png'),
(7, 'FYP/images/7Front.png'),
(7, 'FYP/images/7Back.png'),
(8, 'FYP/images/1Front.jpeg'),
(8, 'FYP/images/1Back.jpeg'),
(9, 'FYP/images/2Front.jpeg'),
(9, 'FYP/images/2Back.jpeg'),
(10, 'FYP/images/3Front.jpeg'),
(10, 'FYP/images/3Back.jpeg'),
(11, 'FYP/images/4Front.jpeg'),
(11, 'FYP/images/4Back.jpeg'),
(12, 'FYP/images/5Front.jpeg'),
(12, 'FYP/images/5Back.jpeg'),
(13, 'FYP/images/6Front.jpeg'),
(13, 'FYP/images/6Back.jpeg'),
(14, 'FYP/images/7Front.jpeg'),
(14, 'FYP/images/7Back.jpeg');

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

-- Insert ORDER_STATUS data
INSERT INTO ORDER_STATUS (O_Status) VALUES 
('Pending'),
('Processing'),
('Shipped'),
('Delivered'),
('Cancelled');

-- Insert DELIVERY_STATUS data
INSERT INTO DELIVERY_STATUS (D_Status) VALUES 
('Preparing'),
('Shipped'),
('In Transit'),
('Out for Delivery'),
('Delivered'),
('Failed Delivery');

-- Insert sample users
INSERT INTO USER (U_FName, U_LName, U_DOB, U_Gender, U_Email, U_Password, U_PNumber, U_SecurityQuestion, U_SecurityAnswer) VALUES
('John', 'Doe', '1990-05-15', 'Male', 'john.doe@example.com', 'hashed_password1', '0123456789', 'What is your pet name?', 'Fluffy'),
('Jane', 'Smith', '1985-08-22', 'Female', 'jane.smith@example.com', 'hashed_password2', '9876543210', 'What city were you born in?', 'New York'),
('Michael', 'Johnson', '1992-03-10', 'Male', 'michael.j@example.com', 'hashed_password3', '0112233445', 'Your first school?', 'Sunshine Primary'),
('Sarah', 'Williams', '1988-11-28', 'Female', 'sarah.w@example.com', 'hashed_password4', '0556677889', 'Mother maiden name?', 'Anderson'),
('David', 'Brown', '1995-07-03', 'Male', 'david.b@example.com', 'hashed_password5', '0334455667', 'Favorite movie?', 'Inception');

-- Insert addresses for users
INSERT INTO ADDRESS (U_ID, AD_Details, AD_City, AD_State, AD_ZipCode) VALUES
(1, '123 Main Street, Apt 4B', 'Kuala Lumpur', 'Wilayah Persekutuan', '50480'),
(1, '456 Oak Avenue', 'Petaling Jaya', 'Selangor', '47800'),
(2, '789 Pine Road', 'Penang', 'Penang', '10050'),
(3, '321 Maple Lane', 'Johor Bahru', 'Johor', '80100'),
(4, '654 Cedar Street', 'Kuching', 'Sarawak', '93000'),
(5, '987 Elm Boulevard', 'Ipoh', 'Perak', '31400');

-- Insert some vouchers
INSERT INTO VOUCHER (V_Code, V_Discount, V_ExpiryDate, V_UsageLimit) VALUES
('WELCOME10', 10.00, '2023-12-31', 100),
('SUMMER20', 20.00, '2023-09-30', 50),
('FREESHIP', 15.00, '2023-10-15', 200),
('NEWUSER25', 25.00, '2023-11-30', 75);

-- Insert sample orders
INSERT INTO ORDERS (U_ID, AD_ID, OS_ID, O_TotalAmount, O_DC) VALUES
(1, 1, 1, 159.80, 0.00),  -- Order 1: Pending
(1, 2, 3, 249.70, 15.00),  -- Order 2: Shipped (used FREESHIP voucher)
(2, 3, 4, 169.90, 0.00),   -- Order 3: Delivered
(3, 4, 2, 319.60, 25.00),  -- Order 4: Processing (used NEWUSER25 voucher)
(4, 5, 1, 89.90, 0.00),    -- Order 5: Pending
(5, 6, 4, 509.70, 20.00);  -- Order 6: Delivered (used SUMMER20 voucher)

-- Record voucher usage
INSERT INTO ORDER_VOUCHER (O_ID, V_ID) VALUES
(2, 3),  -- Order 2 used FREESHIP
(4, 4),  -- Order 4 used NEWUSER25
(6, 2);  -- Order 6 used SUMMER20

-- Insert order items
INSERT INTO ORDER_ITEMS (O_ID, P_ID, PV_ID, OI_Quantity, OI_Price) VALUES
-- Order 1: 2 items (Black Oversized T and White Hoodie)
(1, 2, 5, 1, 89.90),  -- Black Oversized T (M size)
(1, 8, 29, 1, 169.90), -- White Hoodie (M size)

-- Order 2: 1 item (Red Hoodie)
(2, 12, 45, 1, 169.90), -- Red Hoodie (L size)
(2, 1, 2, 1, 69.90),    -- White Oversized T (M size)
(2, 6, 22, 1, 69.90),   -- Grey Oversized T (XL size)

-- Order 3: 1 item (Charcoal Hoodie)
(3, 10, 37, 1, 169.90), -- Charcoal Hoodie (S size)

-- Order 4: 2 items (2 Black Hoodies)
(4, 11, 41, 2, 169.90), -- Black Hoodie (M size)

-- Order 5: 1 item (Red Oversized T)
(5, 3, 9, 1, 79.90),    -- Red Oversized T (S size)

-- Order 6: Multiple items
(6, 7, 25, 1, 79.90),   -- Orchid Oversized T (L size)
(6, 9, 33, 2, 169.90),  -- Grey Hoodie (M size)
(6, 14, 53, 1, 169.90); -- Navy Hoodie (XL size)

-- Insert payment records
INSERT INTO PAYMENT (O_ID, payment_date, payment_method, amount, payment_status, transaction_id) VALUES
(1, '2023-07-15 10:30:00', 'Credit Card', 159.80, 'Completed', 'PAY123456789'),
(2, '2023-07-16 14:45:00', 'GrabPay', 234.70, 'Completed', 'PAY987654321'),
(3, '2023-07-10 09:15:00', 'Credit Card', 169.90, 'Completed', 'PAY456789123'),
(4, '2023-07-18 16:20:00', 'Touch n Go', 294.60, 'Completed', 'PAY321654987'),
(5, '2023-07-19 11:10:00', 'Credit Card', 89.90, 'Pending', 'PAY789123456'),
(6, '2023-07-12 13:25:00', 'ShopeePay', 489.70, 'Completed', 'PAY654321789');

-- Insert delivery records
INSERT INTO DELIVERY (O_ID, D_Carrier, D_TrackingNumber, D_EstimatedDelivery, DS_ID) VALUES
(1, 'J&T Express', 'JNT123456789', '2023-07-20', 1),  -- Preparing
(2, 'Pos Laju', 'POS987654321', '2023-07-18', 3),    -- In Transit
(3, 'DHL', 'DHL456789123', '2023-07-12', 5),        -- Delivered
(4, 'Ninja Van', 'NJV321654987', '2023-07-22', 2),  -- Shipped
(5, 'J&T Express', 'JNT789123456', '2023-07-23', 1),-- Preparing
(6, 'Pos Laju', 'POS654321789', '2023-07-15', 5);   -- Delivered

-- Update some deliveries with actual delivery dates
UPDATE DELIVERY SET D_ActualDelivery = '2023-07-12 15:30:00' WHERE D_ID = 3;
UPDATE DELIVERY SET D_ActualDelivery = '2023-07-15 11:45:00' WHERE D_ID = 6;