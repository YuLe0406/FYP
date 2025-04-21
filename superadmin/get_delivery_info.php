<?php
include 'db.php';

$orderId = $_GET['order_id'];

$deliveryQuery = "
    SELECT D.*, DS.D_Status
    FROM DELIVERY D
    JOIN DELIVERY_STATUS DS ON D.DS_ID = DS.DS_ID
    WHERE D.O_ID = $orderId
";
$deliveryResult = mysqli_query($conn, $deliveryQuery);

if (mysqli_num_rows($deliveryResult) > 0) {
    $delivery = mysqli_fetch_assoc($deliveryResult);
    
    echo "<p><strong>Delivery ID:</strong> {$delivery['D_ID']}</p>";
    echo "<p><strong>Status:</strong> <span class='status " . str_replace(' ', '-', $delivery['D_Status']) . "'>{$delivery['D_Status']}</span></p>";
    echo "<p><strong>Carrier:</strong> {$delivery['D_Carrier']}</p>";
    if ($delivery['D_TrackingNumber']) {
        echo "<p><strong>Tracking Number:</strong> {$delivery['D_TrackingNumber']}</p>";
    }
    echo "<p><strong>Start Date:</strong> " . date('M d, Y', strtotime($delivery['D_StartDate'])) . "</p>";
    echo "<p><strong>Estimated Delivery:</strong> " . date('M d, Y', strtotime($delivery['D_EstimatedDelivery'])) . "</p>";
    if ($delivery['D_ActualDelivery']) {
        echo "<p><strong>Actual Delivery:</strong> " . date('M d, Y H:i', strtotime($delivery['D_ActualDelivery'])) . "</p>";
    }
} else {
    echo "<p>No delivery information available yet.</p>";
}
?>