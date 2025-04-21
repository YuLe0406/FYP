<?php
include 'db.php';

header('Content-Type: application/json');

// Get form data
$orderId = $_POST['order_id'];
$deliveryId = $_POST['delivery_id'];
$carrier = $_POST['carrier'];
$trackingNumber = $_POST['tracking_number'];
$status = str_replace('-', ' ', $_POST['status']); // Convert back from select value
$estimatedDelivery = $_POST['estimated_delivery'];
$actualDelivery = $_POST['actual_delivery'] ?: null;

// Get DS_ID for the status
$statusQuery = "SELECT DS_ID FROM DELIVERY_STATUS WHERE D_Status = '$status'";
$statusResult = mysqli_query($conn, $statusQuery);
$statusRow = mysqli_fetch_assoc($statusResult);
$dsId = $statusRow['DS_ID'];

if ($deliveryId) {
    // Update existing delivery
    $updateQuery = "
        UPDATE DELIVERY SET
            D_Carrier = '$carrier',
            D_TrackingNumber = " . ($trackingNumber ? "'$trackingNumber'" : "NULL") . ",
            D_EstimatedDelivery = '$estimatedDelivery',
            D_ActualDelivery = " . ($actualDelivery ? "'$actualDelivery'" : "NULL") . ",
            DS_ID = $dsId
        WHERE D_ID = $deliveryId
    ";
} else {
    // Create new delivery record
    $updateQuery = "
        INSERT INTO DELIVERY (
            O_ID, D_Carrier, D_TrackingNumber, 
            D_EstimatedDelivery, D_ActualDelivery, DS_ID
        ) VALUES (
            $orderId, '$carrier', " . ($trackingNumber ? "'$trackingNumber'" : "NULL") . ",
            '$estimatedDelivery', " . ($actualDelivery ? "'$actualDelivery'" : "NULL") . ", $dsId
        )
    ";
}

if (mysqli_query($conn, $updateQuery)) {
    // Update order status if delivered
    if ($status == 'Delivered') {
        $osQuery = "SELECT OS_ID FROM ORDER_STATUS WHERE O_Status = 'Delivered'";
        $osResult = mysqli_query($conn, $osQuery);
        $osRow = mysqli_fetch_assoc($osResult);
        $osId = $osRow['OS_ID'];
        
        mysqli_query($conn, "UPDATE ORDERS SET OS_ID = $osId WHERE O_ID = $orderId");
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}
?>