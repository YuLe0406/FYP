<?php
include 'db.php';

header('Content-Type: application/json');

$orderId = intval($_POST['order_id']);
$status = $_POST['status'];
$carrierId = $_POST['carrier'] ?? null;
$trackingNumber = $_POST['tracking_number'] ?? null;
$estimatedDelivery = $_POST['estimated_delivery'] ?? null;

try {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Update order status
    $orderUpdate = "UPDATE ORDERS SET O_Status = ? WHERE O_ID = ?";
    $stmt = mysqli_prepare($conn, $orderUpdate);
    mysqli_stmt_bind_param($stmt, "si", $status, $orderId);
    mysqli_stmt_execute($stmt);
    
    // Handle delivery information if status is Shipped
    if ($status === 'Shipped') {
        // Check if delivery record exists
        $deliveryCheck = "SELECT D_ID FROM DELIVERY WHERE O_ID = $orderId";
        $deliveryResult = mysqli_query($conn, $deliveryCheck);
        
        if (mysqli_num_rows($deliveryResult) > 0) {
            // Update existing delivery
            $deliveryUpdate = "
                UPDATE DELIVERY 
                SET DC_ID = ?, D_TrackingNumber = ?, D_EstimatedDelivery = ?, D_Status = 'Shipped'
                WHERE O_ID = ?
            ";
            $stmt = mysqli_prepare($conn, $deliveryUpdate);
            mysqli_stmt_bind_param($stmt, "issi", $carrierId, $trackingNumber, $estimatedDelivery, $orderId);
            mysqli_stmt_execute($stmt);
        } else {
            // Create new delivery
            $deliveryInsert = "
                INSERT INTO DELIVERY 
                (O_ID, DC_ID, D_TrackingNumber, D_EstimatedDelivery, D_Status)
                VALUES (?, ?, ?, ?, 'Shipped')
            ";
            $stmt = mysqli_prepare($conn, $deliveryInsert);
            mysqli_stmt_bind_param($stmt, "iiss", $orderId, $carrierId, $trackingNumber, $estimatedDelivery);
            mysqli_stmt_execute($stmt);
        }
    }
    
    // If order is delivered, update delivery status
    if ($status === 'Delivered') {
        $deliveryComplete = "
            UPDATE DELIVERY 
            SET D_ActualDelivery = NOW(), D_Status = 'Delivered'
            WHERE O_ID = ?
        ";
        $stmt = mysqli_prepare($conn, $deliveryComplete);
        mysqli_stmt_bind_param($stmt, "i", $orderId);
        mysqli_stmt_execute($stmt);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}