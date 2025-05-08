<?php
include 'db.php';

$orderId = intval($_GET['order_id']);

// Get order details
$orderQuery = "SELECT * FROM ORDERS WHERE O_ID = $orderId";
$orderResult = mysqli_query($conn, $orderQuery);
$order = mysqli_fetch_assoc($orderResult);

// Get customer details
$customerQuery = "SELECT * FROM USER WHERE U_ID = {$order['U_ID']}";
$customerResult = mysqli_query($conn, $customerQuery);
$customer = mysqli_fetch_assoc($customerResult);

// Get address details
$addressQuery = "SELECT * FROM USER_ADDRESS WHERE UA_ID = {$order['UA_ID']}";
$addressResult = mysqli_query($conn, $addressQuery);
$address = mysqli_fetch_assoc($addressResult);

// Display order details
echo "<h3>Order #{$orderId}</h3>";
echo "<p><strong>Customer:</strong> {$customer['U_FName']} {$customer['U_LName']}</p>";
echo "<p><strong>Email:</strong> {$customer['U_Email']}</p>";
echo "<p><strong>Phone:</strong> {$customer['U_PNumber']}</p>";
echo "<p><strong>Order Date:</strong> " . date('M d, Y H:i', strtotime($order['O_Date'])) . "</p>";
echo "<p><strong>Total Amount:</strong> RM" . number_format($order['O_TotalAmount'], 2) . "</p>";
echo "<p><strong>Status:</strong> <span class='status {$order['O_Status']}'>{$order['O_Status']}</span></p>";

// Display address
echo "<h4>Shipping Address</h4>";
echo "<p>{$address['UA_Address1']}<br>";
if (!empty($address['UA_Address2'])) echo "{$address['UA_Address2']}<br>";
echo "{$address['UA_Postcode']} {$address['UA_City']}<br>";
echo "{$address['UA_State']}</p>";

// Display order items
echo "<h4>Order Items</h4>";
echo "<table border='1' cellpadding='8' width='100%'>";
echo "<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>";

$itemsQuery = "
    SELECT P.P_Name, OI.OI_Quantity, OI.OI_Price
    FROM ORDER_ITEMS OI
    JOIN PRODUCT P ON OI.P_ID = P.P_ID
    WHERE OI.O_ID = $orderId
";
$itemsResult = mysqli_query($conn, $itemsQuery);

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $subtotal = $item['OI_Quantity'] * $item['OI_Price'];
    echo "<tr>
            <td>{$item['P_Name']}</td>
            <td>{$item['OI_Quantity']}</td>
            <td>RM" . number_format($item['OI_Price'], 2) . "</td>
            <td>RM" . number_format($subtotal, 2) . "</td>
          </tr>";
}

echo "</table>";