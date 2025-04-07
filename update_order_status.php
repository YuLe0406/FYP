<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['orderStatus']);

    // If delivery row exists, update it; else, insert it
    $checkQuery = "SELECT * FROM DELIVERY WHERE O_ID = $orderId";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $updateQuery = "UPDATE DELIVERY SET D_Status = '$status' WHERE O_ID = $orderId";
    } else {
        $estimatedDate = date('Y-m-d', strtotime('+3 days'));
        $updateQuery = "INSERT INTO DELIVERY (O_ID, D_Status, D_EstimatedDelivery) VALUES ($orderId, '$status', '$estimatedDate')";
    }

    if (mysqli_query($conn, $updateQuery)) {
        header("Location: admin_orders.php?success=1");
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
}
?>
