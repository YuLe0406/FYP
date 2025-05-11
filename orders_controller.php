<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'db.php';

$userId = $_SESSION['user_id'];

// Handle count-only requests first
if (isset($_GET['count_only']) && $_GET['count_only'] === 'true') {
    $status = isset($_GET['status']) ? $_GET['status'] : 'Processing';
    $validStatuses = ['Processing', 'Shipped', 'Delivered'];
    
    if (!in_array($status, $validStatuses)) {
        $status = 'Processing';
    }
    
    $countQuery = "SELECT COUNT(*) as count FROM ORDERS WHERE U_ID = ? AND O_Status = ?";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param("is", $userId, $status);
    $countStmt->execute();
    $result = $countStmt->get_result();
    $row = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode(['count' => $row['count']]);
    exit();
}

// Handle regular order requests
$status = isset($_GET['status']) ? $_GET['status'] : 'Processing';
$validStatuses = ['Processing', 'Shipped', 'Delivered'];

if (!in_array($status, $validStatuses)) {
    $status = 'Processing';
}

function getOrdersByStatus($conn, $userId, $status) {
    $orderQuery = "SELECT O_ID, O_Date, O_TotalAmount, O_Status 
                  FROM ORDERS 
                  WHERE U_ID = ? AND O_Status = ?
                  ORDER BY O_Date DESC";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("is", $userId, $status);
    $stmt->execute();
    return $stmt->get_result();
}

function getOrderItems($conn, $orderId) {
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
    return $itemsStmt->get_result();
}

$orders = getOrdersByStatus($conn, $userId, $status);

if ($orders->num_rows === 0) {
    echo '<div class="no-orders">
            <i class="fas fa-box-open"></i>
            <p>No '.strtolower($status).' orders found.</p>';
    
    if ($status === 'Processing') {
        echo '<a href="shop.php" class="btn">Continue Shopping</a>';
    }
    echo '</div>';
} else {
    while ($order = $orders->fetch_assoc()) {
        $orderId = $order['O_ID'];
        $orderDate = date('F j, Y, g:i a', strtotime($order['O_Date']));
        $orderTotal = number_format($order['O_TotalAmount'], 2);
        $status = $order['O_Status'];
        $statusClass = strtolower(str_replace(' ', '-', $status));
        
        echo '<div class="order-card">
                <div class="order-header">
                    <div class="order-id">Order #' . $orderId . '</div>
                    <div class="order-date">Placed on ' . $orderDate . '</div>
                    <div class="order-status status-' . $statusClass . '">' . $status . '</div>
                </div>
                <div class="order-items">';

        $items = getOrderItems($conn, $orderId);
        while ($item = $items->fetch_assoc()) {
            $itemTotal = number_format($item['OI_Price'] * $item['OI_Quantity'], 2);
            $imagePath = 'http://localhost/FYP/' . $item['P_Picture'];
            
            echo '<div class="order-item">
                    <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($item['P_Name']) . '" class="item-image">
                    <div class="item-details">
                        <div class="item-name">' . htmlspecialchars($item['P_Name']) . '</div>
                        <div class="item-price">RM ' . $itemTotal . '</div>
                        <div style="clear:both;"></div>
                        <div class="item-attributes">';
            
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
                
            echo '      <div>Quantity: ' . $item['OI_Quantity'] . '</div>
                    </div>
                </div>';
        }

        echo '</div>
              <div class="order-total">
                Order Total: RM ' . $orderTotal . '
              </div>
            </div>';
    }
}
?>