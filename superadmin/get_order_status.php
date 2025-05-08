<?php
include 'db.php';

$orderId = intval($_GET['order_id']);

$response = [
    'order_status' => '',
    'carrier_id' => '',
    'tracking_number' => '',
    'estimated_delivery' => ''
];

// Get order status
$orderQuery = "SELECT O_Status FROM ORDERS WHERE O_ID = $orderId";
$orderResult = mysqli_query($conn, $orderQuery);
if ($orderResult && mysqli_num_rows($orderResult) > 0) {
    $order = mysqli_fetch_assoc($orderResult);
    $response['order_status'] = $order['O_Status'];
}

// Get delivery info
$deliveryQuery = "SELECT * FROM DELIVERY WHERE O_ID = $orderId";
$deliveryResult = mysqli_query($conn, $deliveryQuery);
if ($deliveryResult && mysqli_num_rows($deliveryResult) > 0) {
    $delivery = mysqli_fetch_assoc($deliveryResult);
    $response['carrier_id'] = $delivery['DC_ID'];
    $response['tracking_number'] = $delivery['D_TrackingNumber'];
    $response['estimated_delivery'] = $delivery['D_EstimatedDelivery'];
}

header('Content-Type: application/json');
echo json_encode($response);