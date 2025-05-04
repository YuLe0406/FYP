-- Active: 1742473518413@@127.0.0.1@3306@ctrlx
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `O_ID` int(11) NOT NULL,
  `U_ID` int(11) NOT NULL,
  `UA_ID` int(11) DEFAULT NULL,
  `OS_ID` int(11) NOT NULL,
  `O_TotalAmount` decimal(10,2) NOT NULL,
  `O_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`O_ID`, `U_ID`, `UA_ID`, `OS_ID`, `O_TotalAmount`, `O_Date`) VALUES
(1, 4, 4, 4, 449.50, '2025-05-02 08:54:12'),
(2, 4, 3, 1, 89.90, '2025-05-02 09:03:30'),
(3, 5, 6, 1, 279.60, '2025-05-02 12:18:48'),
(4, 4, 3, 1, 169.90, '2025-05-02 13:19:00'),
(5, 4, 3, 1, 169.90, '2025-05-02 13:19:51'),
(6, 4, 3, 1, 169.90, '2025-05-02 13:21:12'),
(7, 4, 3, 1, 139.80, '2025-05-02 13:45:29'),
(8, 4, 3, 1, 139.80, '2025-05-02 13:45:51'),
(9, 4, 3, 1, 139.80, '2025-05-02 15:07:31'),
(10, 4, 3, 1, 79.90, '2025-05-02 15:49:01'),
(11, 4, 3, 1, 69.90, '2025-05-02 16:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `OI_ID` int(11) NOT NULL,
  `O_ID` int(11) NOT NULL,
  `P_ID` int(11) NOT NULL,
  `PV_ID` int(11) DEFAULT NULL,
  `OI_Quantity` int(11) NOT NULL,
  `OI_Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`OI_ID`, `O_ID`, `P_ID`, `PV_ID`, `OI_Quantity`, `OI_Price`) VALUES
(35, 30, 1, 2, 5, 69.90),
(36, 31, 1, 4, 2, 69.90),
(37, 1, 2, 8, 5, 89.90),
(38, 2, 2, 8, 1, 89.90),
(39, 3, 1, 1, 4, 69.90),
(40, 4, 11, 44, 1, 169.90),
(41, 5, 11, 44, 1, 169.90),
(42, 6, 11, 44, 1, 169.90),
(43, 7, 1, 2, 2, 69.90),
(44, 8, 1, 2, 2, 69.90),
(45, 9, 1, 2, 2, 69.90),
(46, 10, 3, 9, 1, 79.90),
(47, 11, 1, 4, 1, 69.90);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `OS_ID` int(11) NOT NULL,
  `O_Status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`OS_ID`, `O_Status`) VALUES
(1, 'Pending'),
(2, 'Processing'),
(3, 'Shipped'),
(4, 'Delivered'),
(5, 'Cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Pay_ID` int(11) NOT NULL,
  `O_ID` int(11) NOT NULL,
  `Pay_Method` enum('Credit Card','PayPal') DEFAULT 'Credit Card',
  `Pay_Amount` decimal(10,2) NOT NULL,
  `Pay_CardNumber` varchar(20) NOT NULL,
  `Pay_ExpiryDate` varchar(7) NOT NULL,
  `Pay_CVV` varchar(4) NOT NULL,
  `Pay_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Pay_ID`, `O_ID`, `Pay_Method`, `Pay_Amount`, `Pay_CardNumber`, `Pay_ExpiryDate`, `Pay_CVV`, `Pay_Date`) VALUES
(29, 1, 'Credit Card', 449.50, '6555657653756673', '12/30', '987', '2025-05-02 08:54:12'),
(30, 2, 'Credit Card', 89.90, '4542524556845866', '12/33', '568', '2025-05-02 09:03:30'),
(31, 3, 'PayPal', 279.60, 'N/A', 'N/A', 'N/A', '2025-05-02 12:18:48'),
(32, 4, 'Credit Card', 169.90, '4626652656456426', '12/29', '567', '2025-05-02 13:19:00'),
(33, 5, 'Credit Card', 169.90, '5464645245652445', '12/29', '787', '2025-05-02 13:19:51'),
(34, 6, 'PayPal', 169.90, 'N/A', 'N/A', 'N/A', '2025-05-02 13:21:12'),
(35, 7, 'Credit Card', 139.80, '6840480565026024', '12/29', '547', '2025-05-02 13:45:29'),
(36, 8, 'PayPal', 139.80, 'N/A', 'N/A', 'N/A', '2025-05-02 13:45:51'),
(37, 9, 'PayPal', 139.80, 'N/A', 'N/A', 'N/A', '2025-05-02 15:07:31'),
(38, 10, 'PayPal', 79.90, 'N/A', 'N/A', 'N/A', '2025-05-02 15:49:01'),
(39, 11, 'PayPal', 69.90, 'N/A', 'N/A', 'N/A', '2025-05-02 16:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `P_ID` int(11) NOT NULL,
  `C_ID` int(11) NOT NULL,
  `P_Name` varchar(255) NOT NULL,
  `P_Price` decimal(10,2) NOT NULL,
  `P_Picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`P_ID`, `C_ID`, `P_Name`, `P_Price`, `P_Picture`) VALUES
(1, 1, 'White Oversized T', 69.90, 'images/1front.png'),
(2, 1, 'Black Oversized T', 89.90, 'images/2front.png'),
(3, 1, 'Red Oversized T', 79.90, 'images/3front.png'),
(4, 1, 'Clay Oversized T', 79.90, 'images/4front.png'),
(5, 1, 'Butter Oversized T', 79.90, 'images/5front.png'),
(6, 1, 'Grey Oversized T', 69.90, 'images/6front.png'),
(7, 1, 'Orchid Oversized T', 79.90, 'images/7front.png'),
(8, 2, 'White Hoodie', 169.90, 'images/1Front.jpeg'),
(9, 2, 'Grey Hoodie', 169.90, 'images/2Front.jpeg'),
(10, 2, 'Charcoal Hoodie', 169.90, 'images/3Front.jpeg'),
(11, 2, 'Black Hoodie', 169.90, 'images/4Front.jpeg'),
(12, 2, 'Red Hoodie', 169.90, 'images/5Front.jpeg'),
(13, 2, 'Green Hoodie', 169.90, 'images/6Front.jpeg'),
(14, 2, 'Navy Hoodie', 169.90, 'images/7Front.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `PV_ID` int(11) NOT NULL,
  `P_ID` int(11) NOT NULL,
  `P_Color` varchar(50) NOT NULL,
  `P_Size` varchar(50) NOT NULL,
  `P_Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`PV_ID`, `P_ID`, `P_Color`, `P_Size`, `P_Quantity`) VALUES
(1, 1, 'White', 'S', 4),
(2, 1, 'White', 'M', 0),
(3, 1, 'White', 'L', 5),
(4, 1, 'White', 'XL', 2),
(5, 2, 'Black', 'S', 8),
(6, 2, 'Black', 'M', 12),
(7, 2, 'Black', 'L', 8),
(8, 2, 'Black', 'XL', 7),
(9, 3, 'Red', 'S', 9),
(10, 3, 'Red', 'M', 12),
(11, 3, 'Red', 'L', 8),
(12, 3, 'Red', 'XL', 6),
(13, 4, 'Clay', 'S', 10),
(14, 4, 'Clay', 'M', 12),
(15, 4, 'Clay', 'L', 8),
(16, 4, 'Clay', 'XL', 6),
(17, 5, 'Butter', 'S', 10),
(18, 5, 'Butter', 'M', 12),
(19, 5, 'Butter', 'L', 8),
(20, 5, 'Butter', 'XL', 6),
(21, 6, 'Grey', 'S', 10),
(22, 6, 'Grey', 'M', 12),
(23, 6, 'Grey', 'L', 8),
(24, 6, 'Grey', 'XL', 6),
(25, 7, 'Orchid', 'S', 10),
(26, 7, 'Orchid', 'M', 12),
(27, 7, 'Orchid', 'L', 8),
(28, 7, 'Orchid', 'XL', 6),
(29, 8, 'White', 'S', 5),
(30, 8, 'White', 'M', 7),
(31, 8, 'White', 'L', 4),
(32, 8, 'White', 'XL', 3),
(33, 9, 'Grey', 'S', 5),
(34, 9, 'Grey', 'M', 7),
(35, 9, 'Grey', 'L', 4),
(36, 9, 'Grey', 'XL', 3),
(37, 10, 'Charcoal', 'S', 5),
(38, 10, 'Charcoal', 'M', 7),
(39, 10, 'Charcoal', 'L', 4),
(40, 10, 'Charcoal', 'XL', 3),
(41, 11, 'Black', 'S', 5),
(42, 11, 'Black', 'M', 7),
(43, 11, 'Black', 'L', 4),
(44, 11, 'Black', 'XL', 0),
(45, 12, 'Red', 'S', 5),
(46, 12, 'Red', 'M', 7),
(47, 12, 'Red', 'L', 4),
(48, 12, 'Red', 'XL', 3),
(49, 13, 'Green', 'S', 5),
(50, 13, 'Green', 'M', 7),
(51, 13, 'Green', 'L', 4),
(52, 13, 'Green', 'XL', 3),
(53, 14, 'Navy', 'S', 5),
(54, 14, 'Navy', 'M', 7),
(55, 14, 'Navy', 'L', 4),
(56, 14, 'Navy', 'XL', 9);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `U_ID` int(11) NOT NULL,
  `U_FName` varchar(50) NOT NULL,
  `U_LName` varchar(50) NOT NULL,
  `U_Email` varchar(100) NOT NULL,
  `U_Password` varchar(255) NOT NULL,
  `U_PNumber` varchar(15) NOT NULL,
  `U_Address` varchar(255) DEFAULT NULL,
  `U_DOB` date NOT NULL,
  `U_Gender` enum('male','female','other') NOT NULL,
  `U_SecurityQuestion` varchar(255) NOT NULL,
  `U_SecurityAnswer` varchar(255) NOT NULL,
  `U_ResetToken` varchar(64) DEFAULT NULL,
  `U_ResetTokenExpiry` datetime DEFAULT NULL,
  `U_AccountCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `U_LastUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`U_ID`, `U_FName`, `U_LName`, `U_Email`, `U_Password`, `U_PNumber`, `U_Address`, `U_DOB`, `U_Gender`, `U_SecurityQuestion`, `U_SecurityAnswer`, `U_ResetToken`, `U_ResetTokenExpiry`, `U_AccountCreated`, `U_LastUpdated`) VALUES
(4, 'TAN', 'YU LE', 'gamingsham0406@gmail.com', 'Tyl0406$', '01082800260', '26, JALAN NB2 14/1, TAMAN NUSA BESTARI 2', '2005-06-04', 'male', 'What city were you born in?', 'JB', NULL, NULL, '2025-04-29 07:42:06', '2025-05-02 14:53:16'),
(5, 'JAMES', 'BOND', 'james007@gmail.com', 'Jbond007!', '01010071007', '41 Liverpool Road, Chester, Cheshire, England', '1968-03-02', 'male', 'What city were you born in?', 'England', NULL, NULL, '2025-05-02 12:04:30', '2025-05-02 12:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `UA_ID` int(11) NOT NULL,
  `U_ID` int(11) NOT NULL,
  `UA_Type` enum('home','work','other') NOT NULL DEFAULT 'home',
  `UA_Address1` varchar(255) NOT NULL,
  `UA_Address2` varchar(255) DEFAULT NULL,
  `UA_Postcode` varchar(10) NOT NULL,
  `UA_City` varchar(100) NOT NULL,
  `UA_State` enum('Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Penang','Perak','Perlis','Sabah','Sarawak','Selangor','Terengganu') NOT NULL,
  `UA_IsDefault` tinyint(1) NOT NULL DEFAULT 0,
  `UA_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `UA_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`UA_ID`, `U_ID`, `UA_Type`, `UA_Address1`, `UA_Address2`, `UA_Postcode`, `UA_City`, `UA_State`, `UA_IsDefault`, `UA_Created`, `UA_Updated`) VALUES
(3, 4, 'home', '26, JALAN NB2 14/1, TAMAN NUSA BESTARI 2', '', '81300', 'Skudai', 'Johor', 1, '2025-05-02 08:47:21', '2025-05-02 08:47:21'),
(4, 4, 'home', 'Jalan D1', 'Ixora Apartment', '75450', 'Ayer Keroh', 'Melaka', 0, '2025-05-02 08:48:13', '2025-05-02 08:48:44'),
(6, 5, 'home', '007, Jalan England', 'Suite', '81300', 'Johor Bahru', 'Johor', 1, '2025-05-02 12:17:57', '2025-05-02 12:17:57');

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`O_ID`),
  ADD KEY `U_ID` (`U_ID`),
  ADD KEY `OS_ID` (`OS_ID`),
  ADD KEY `UA_ID` (`UA_ID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`OI_ID`),
  ADD KEY `O_ID` (`O_ID`),
  ADD KEY `P_ID` (`P_ID`),
  ADD KEY `PV_ID` (`PV_ID`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`OS_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Pay_ID`),
  ADD KEY `O_ID` (`O_ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`P_ID`),
  ADD KEY `C_ID` (`C_ID`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`PV_ID`),
  ADD KEY `P_ID` (`P_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`U_ID`),
  ADD UNIQUE KEY `U_Email` (`U_Email`),
  ADD KEY `idx_user_email` (`U_Email`),
  ADD KEY `idx_reset_token` (`U_ResetToken`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`UA_ID`),
  ADD KEY `idx_user_address` (`U_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `O_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `OI_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `OS_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Pay_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `P_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `PV_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `U_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `UA_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`U_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`OS_ID`) REFERENCES `order_status` (`OS_ID`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`UA_ID`) REFERENCES `user_address` (`UA_ID`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`O_ID`) REFERENCES `orders` (`O_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`P_ID`) REFERENCES `product` (`P_ID`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`PV_ID`) REFERENCES `product_variants` (`PV_ID`) ON DELETE SET NULL;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`O_ID`) REFERENCES `orders` (`O_ID`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`C_ID`) REFERENCES `categories` (`C_ID`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`P_ID`) REFERENCES `product` (`P_ID`) ON DELETE CASCADE;

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`U_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE;