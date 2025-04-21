<?php
include 'db.php';

$orderId = $_GET['order_id'];

// Get order details
$orderQuery = "
    SELECT O.*, OS.O_Status, U.U_FName, U.U_LName, U.U_Email, U.U_PNumber,
           A.AD_Details, A.AD_City, A.AD_State, A.AD_ZipCode,
           P.payment_method, P.payment_status, P.transaction_id
    FROM ORDERS O
    JOIN ORDER_STATUS OS ON O.OS_ID = OS.OS_ID
    JOIN USER U ON O.U_ID = U.U_ID
    JOIN ADDRESS A ON O.AD_ID = A.AD_ID
    LEFT JOIN PAYMENT P ON O.O_ID = P.O_ID
    WHERE O.O_ID = $orderId
";
$orderResult = mysqli_query($conn, $orderQuery);
$order = mysqli_fetch_assoc($orderResult);

// Get order items
$itemsQuery = "
    SELECT P.P_Name, OI.OI_Quantity, OI.OI_Price, 
           PV.P_Size, PC.COLOR_NAME, PC.COLOR_HEX
    FROM ORDER_ITEMS OI
    JOIN PRODUCT P ON OI.P_ID = P.P_ID
    LEFT JOIN PRODUCT_VARIANTS PV ON OI.PV_ID = PV.PV_ID
    LEFT JOIN PRODUCT_COLOR PC ON PV.PC_ID = PC.PC_ID
    WHERE OI.O_ID = $orderId
";
$itemsResult = mysqli_query($conn, $itemsQuery);

// Display order details
echo "<div class='order-details'>";
echo "<h3>Order #{$orderId}</h3>";
echo "<p><strong>Customer:</strong> {$order['U_FName']} {$order['U_LName']}</p>";
echo "<p><strong>Email:</strong> {$order['U_Email']}</p>";
echo "<p><strong>Phone:</strong> {$order['U_PNumber']}</p>";
echo "<p><strong>Order Date:</strong> " . date('M d, Y H:i', strtotime($order['O_Date'])) . "</p>";
echo "<p><strong>Status:</strong> <span class='status {$order['O_Status']}'>{$order['O_Status']}</span></p>";
echo "<p><strong>Total Amount:</strong> RM" . number_format($order['O_TotalAmount'], 2) . "</p>";

// Display payment info
echo "<h4>Payment Information</h4>";
echo "<p><strong>Method:</strong> {$order['payment_method']}</p>";
echo "<p><strong>Status:</strong> {$order['payment_status']}</p>";
if (!empty($order['transaction_id'])) {
    echo "<p><strong>Transaction ID:</strong> {$order['transaction_id']}</p>";
}

// Display shipping address
echo "<h4>Shipping Address</h4>";
echo "<p>{$order['AD_Details']}<br>";
echo "{$order['AD_City']}, {$order['AD_State']}<br>";
echo "{$order['AD_ZipCode']}</p>";

// Display order items
echo "<h4>Order Items</h4>";
echo "<table>";
echo "<tr><th>Product</th><th>Color</th><th>Size</th><th>Qty</th><th>Price</th><th>Total</th></tr>";

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $colorName = isset($item['COLOR_NAME']) ? $item['COLOR_NAME'] : 'N/A';
    $colorHex = isset($item['COLOR_HEX']) ? $item['COLOR_HEX'] : '';
    $size = isset($item['P_Size']) ? $item['P_Size'] : 'N/A';
    
    $colorDisplay = $colorName != 'N/A' 
        ? "<span style='display:inline-block;width:15px;height:15px;background-color:{$colorHex};border:1px solid #ccc;vertical-align:middle;margin-right:5px;'></span>{$colorName}" 
        : "N/A";
    
    echo "<tr>";
    echo "<td>{$item['P_Name']}</td>";
    echo "<td>{$colorDisplay}</td>";
    echo "<td>{$size}</td>";
    echo "<td>{$item['OI_Quantity']}</td>";
    echo "<td>RM" . number_format($item['OI_Price'], 2) . "</td>";
    echo "<td>RM" . number_format($item['OI_Price'] * $item['OI_Quantity'], 2) . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";
?>