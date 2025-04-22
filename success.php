<?php
session_start();
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}
$order_id = $_GET['order_id'];
?>

<!DOCTYPE html>
<html>
<head><title>Order Success</title></head>
<body>
    <h1>🎉 Thank you for your order!</h1>
    <p>Your order ID is: <strong>#<?php echo htmlspecialchars($order_id); ?></strong></p>
    <a href="index.php">Return to Homepage</a>
</body>
</html>
