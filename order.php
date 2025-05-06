<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'db.php';
include 'header.php';

$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <style>
        /* Main content padding to account for fixed header */
        main {
            padding-top: 70px;
            min-height: calc(100vh - 160px); /* Adjust based on footer height */
            background-color: #f5f5f5;
        }
        
        .order-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background-color: white;
        }
        
        .order-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }
        
        .order-items {
            padding: 20px;
        }
        
        .order-item {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-right: 20px;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .item-price {
            min-width: 120px;
            text-align: right;
        }
        
        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background-color:rgb(190, 241, 203);
            color:rgb(15, 162, 23);
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-total {
            text-align: right;
            padding: 15px 20px;
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 18px;
            border-top: 1px solid #ddd;
        }
        
        .no-orders {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .item-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .item-attributes {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .item-quantity {
            color: #333;
        }
        
        .order-id {
            font-weight: bold;
            font-size: 18px;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
    </style>
</head>
<body>
    <main>
        <div class="order-container">
            <h1>My Orders</h1>

            <?php
            // Fetch all orders for the current user with status
            $orderQuery = "SELECT o.O_ID, o.O_Date, o.O_TotalAmount, os.O_Status 
                          FROM ORDERS o
                          JOIN ORDER_STATUS os ON o.OS_ID = os.OS_ID
                          WHERE o.U_ID = ?
                          ORDER BY o.O_Date DESC";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $orders = $stmt->get_result();

            if ($orders->num_rows === 0) {
                echo '<div class="no-orders">
                        <p>You haven\'t placed any orders yet.</p>
                        <a href="shop.php" class="btn">Start Shopping</a>
                      </div>';
            } else {
                while ($order = $orders->fetch_assoc()) {
                    $orderId = $order['O_ID'];
                    $orderDate = date('F j, Y, g:i a', strtotime($order['O_Date']));
                    $orderTotal = number_format($order['O_TotalAmount'], 2);
                    $status = $order['O_Status'];
                    $statusClass = strtolower(str_replace(' ', '-', $status));
                    
                    echo '<div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-id">Order #' . $orderId . '</span>
                                    <div class="order-date">Placed on ' . $orderDate . '</div>
                                </div>
                                <div class="order-status status-' . $statusClass . '">
                                    ' . $status . '
                                </div>
                            </div>';

                    // Fetch items for this order
                    $itemsQuery = "SELECT 
                                    p.P_ID, p.P_Name, p.P_Picture, p.P_Price,
                                    pv.P_Size, pv.P_Color,
                                    oi.OI_Quantity, oi.OI_Price
                                  FROM ORDER_ITEMS oi
                                  JOIN PRODUCT p ON oi.P_ID = p.P_ID
                                  LEFT JOIN PRODUCT_VARIANTS pv ON oi.PV_ID = pv.PV_ID
                                  WHERE oi.O_ID = ?";
                    $itemsStmt = $conn->prepare($itemsQuery);
                    $itemsStmt->bind_param("i", $orderId);
                    $itemsStmt->execute();
                    $items = $itemsStmt->get_result();

                    while ($item = $items->fetch_assoc()) {
                        $itemTotal = number_format($item['OI_Price'] * $item['OI_Quantity'], 2);
                        $imagePath = 'http://localhost/FYP/' . $item['P_Picture'];
                        
                        echo '<div class="order-items">
                                <div class="order-item">
                                    <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($item['P_Name']) . '" class="item-image">
                                    <div class="item-details">
                                        <div class="item-name">' . htmlspecialchars($item['P_Name']) . '</div>
                                        <div class="item-attributes">';
                        
                        // Display color and size if available
                        if (!empty($item['P_Color']) || !empty($item['P_Size'])) {
                            echo '<div>';
                            if (!empty($item['P_Color'])) {
                                echo 'Color: ' . htmlspecialchars($item['P_Color']);
                            }
                            if (!empty($item['P_Color']) && !empty($item['P_Size'])) {
                                echo ' | ';
                            }
                            if (!empty($item['P_Size'])) {
                                echo 'Size: ' . htmlspecialchars($item['P_Size']);
                            }
                            echo '</div>';
                        }
                        
                        echo '          <div class="item-quantity">Quantity: ' . $item['OI_Quantity'] . '</div>
                                    </div>
                                    <div class="item-price">
                                        <div>RM ' . number_format($item['OI_Price'], 2) . ' each</div>
                                        <div style="font-weight:bold; margin-top:5px;">RM ' . $itemTotal . '</div>
                                    </div>
                                </div>
                              </div>';
                    }

                    echo '<div class="order-total">
                            Order Total: RM ' . $orderTotal . '
                          </div>
                        </div>';
                }
            }
            ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>