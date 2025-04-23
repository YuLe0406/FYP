<?php
session_start();
require 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Get data from fetch request (JSON)
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart']) || !is_array($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$userId = $_SESSION['user_id'];
$cart = $data['cart'];
$total = $data['total'];
$discount = $data['discount'] ?? 0;
$addressDetails = $data['address'];
$saveAddress = $data['saveAddress'] ?? false;

// Step 1: Save address
$addressId = null;
if ($saveAddress) {
    $stmt = $conn->prepare("INSERT INTO ADDRESS (U_ID, AD_Details, AD_City, AD_State, AD_ZipCode) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $addressDetails['line1'], $addressDetails['city'], $addressDetails['state'], $addressDetails['zipcode']);
    if ($stmt->execute()) {
        $addressId = $stmt->insert_id;
    } else {
        echo json_encode(['error' => 'Failed to save address']);
        exit;
    }
    $stmt->close();
} else {
    // You could get latest address from DB (if you want this logic)
    $result = $conn->query("SELECT AD_ID FROM ADDRESS WHERE U_ID = $userId ORDER BY AD_ID DESC LIMIT 1");
    $row = $result->fetch_assoc();
    $addressId = $row['AD_ID'] ?? null;
}

// Step 2: Save order
$orderStatus = 1; // Default to "Pending" or similar status from ORDER_STATUS table
$stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, AD_ID, OS_ID, O_TotalAmount, O_DC) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiidd", $userId, $addressId, $orderStatus, $total, $discount);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to save order']);
    exit;
}
$orderId = $stmt->insert_id;
$stmt->close();

// Step 3: Save order items
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

echo json_encode(['success' => true, 'order_id' => $orderId]);
?>
