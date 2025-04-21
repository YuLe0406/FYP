<?php
include 'db.php';

header('Content-Type: application/json');

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
    echo json_encode($delivery);
} else {
    echo json_encode(null);
}
?>