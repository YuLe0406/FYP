<?php
include 'db.php';

header('Content-Type: application/json');

$orderId = $_POST['order_id'];
$status = $_POST['status'];

// Get the OS_ID for the new status
$statusQuery = "SELECT OS_ID FROM ORDER_STATUS WHERE O_Status = '$status'";
$statusResult = mysqli_query($conn, $statusQuery);

if (mysqli_num_rows($statusResult) > 0) {
    $statusRow = mysqli_fetch_assoc($statusResult);
    $osId = $statusRow['OS_ID'];
    
    // Update the order status
    $updateQuery = "UPDATE ORDERS SET OS_ID = $osId WHERE O_ID = $orderId";
    
    if (mysqli_query($conn, $updateQuery)) {
        // If status is Delivered, update delivery status to Delivered if exists
        if ($status == 'Delivered') {
            $deliveryQuery = "SELECT D_ID FROM DELIVERY WHERE O_ID = $orderId";
            $deliveryResult = mysqli_query($conn, $deliveryQuery);
            
            if (mysqli_num_rows($deliveryResult) > 0) {
                $deliveryRow = mysqli_fetch_assoc($deliveryResult);
                $dId = $deliveryRow['D_ID'];
                
                // Get DS_ID for Delivered status
                $dsQuery = "SELECT DS_ID FROM DELIVERY_STATUS WHERE D_Status = 'Delivered'";
                $dsResult = mysqli_query($conn, $dsQuery);
                $dsRow = mysqli_fetch_assoc($dsResult);
                $dsId = $dsRow['DS_ID'];
                
                // Update delivery status
                mysqli_query($conn, "UPDATE DELIVERY SET DS_ID = $dsId, D_ActualDelivery = NOW() WHERE D_ID = $dId");
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
}
?>