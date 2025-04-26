<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart']) || !is_array($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$userId = $_SESSION['user_id'];
$cart = $data['cart'];
$total = $data['total'];

// ✅ Step 1: Save order (without address)
$orderStatus = 1; // pending
$stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, OS_ID, O_TotalAmount) VALUES (?, ?, ?)");
$stmt->bind_param("iid", $userId, $orderStatus, $total);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to create order']);
    exit;
}
$orderId = $stmt->insert_id;
$stmt->close();

// ✅ Step 2: Save order items
$stmt = $conn->prepare("INSERT INTO ORDER_ITEMS (O_ID, P_ID, PV_ID, OI_Quantity, OI_Price) VALUES (?, ?, ?, ?, ?)");

foreach ($cart as $item) {
    $pId = $item['id'];
    $pvId = $item['variant_id'] ?? null;
    $qty = $item['quantity'];
    $price = $item['price'];

    $stmt->bind_param("iiiid", $orderId, $pId, $pvId, $qty, $price);
    $stmt->execute();
}
$stmt->close();

// ✅ Done
echo json_encode(['success' => true, 'order_id' => $orderId]);
?>
