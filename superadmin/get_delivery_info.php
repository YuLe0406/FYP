<?php
include 'db.php';

$orderId = intval($_GET['order_id']);

$query = "
    SELECT D.*, DC.DC_Name AS Carrier
    FROM DELIVERY D
    JOIN DELIVERY_CARRIER DC ON D.DC_ID = DC.DC_ID
    WHERE D.O_ID = $orderId
";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $delivery = mysqli_fetch_assoc($result);
    
    echo "<p><strong>Carrier:</strong> {$delivery['Carrier']}</p>";
    echo "<p><strong>Tracking Number:</strong> " . ($delivery['D_TrackingNumber'] ?: 'N/A') . "</p>";
    echo "<p><strong>Status:</strong> {$delivery['D_Status']}</p>";
    echo "<p><strong>Start Date:</strong> " . date('M d, Y', strtotime($delivery['D_StartDate'])) . "</p>";
    echo "<p><strong>Estimated Delivery:</strong> " . date('M d, Y', strtotime($delivery['D_EstimatedDelivery'])) . "</p>";
    
    if ($delivery['D_ActualDelivery']) {
        echo "<p><strong>Actual Delivery:</strong> " . date('M d, Y H:i', strtotime($delivery['D_ActualDelivery'])) . "</p>";
    }
} else {
    echo "<p>No delivery information found for this order.</p>";
}