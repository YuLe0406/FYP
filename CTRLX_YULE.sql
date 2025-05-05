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


CREATE TABLE `product` (
  `P_ID` int(11) NOT NULL,
  `C_ID` int(11) NOT NULL,
  `P_Name` varchar(255) NOT NULL,
  `P_Price` decimal(10,2) NOT NULL,
  `P_Picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `product_variants` (
  `PV_ID` int(11) NOT NULL,
  `P_ID` int(11) NOT NULL,
  `P_Color` varchar(50) NOT NULL,
  `P_Size` varchar(50) NOT NULL,
  `P_Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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


CREATE TABLE `orders` (
  `O_ID` int(11) NOT NULL,
  `U_ID` int(11) NOT NULL,
  `UA_ID` int(11) DEFAULT NULL,
  `OS_ID` int(11) NOT NULL,
  `O_TotalAmount` decimal(10,2) NOT NULL,
  `O_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `order_items` (
  `OI_ID` int(11) NOT NULL,
  `O_ID` int(11) NOT NULL,
  `P_ID` int(11) NOT NULL,
  `PV_ID` int(11) DEFAULT NULL,
  `OI_Quantity` int(11) NOT NULL,
  `OI_Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `order_status` (
  `OS_ID` int(11) NOT NULL,
  `O_Status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;