<?php
header("Content-Type: application/json");
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
$discount = $data['discount'] ?? 0;
$saveAddress = $data['saveAddress'] ?? false;

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// ⬇️ Address Details
$street = $data['address1'];
$city = $data['city'];
$state = $data['state'];
$postcode = $data['postcode'];

// ✅ Step 1: Save address
$addressId = null;
if ($saveAddress) {
    $stmt = $conn->prepare("INSERT INTO USER_ADDRESS (U_ID, UA_Type, UA_Address1, UA_City, UA_State, UA_Postcode) VALUES (?, 'home', ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $street, $city, $state, $postcode);
    if ($stmt->execute()) {
        $addressId = $stmt->insert_id;
    } else {
        echo json_encode(['error' => 'Failed to save address']);
        exit;
    }
    $stmt->close();
} else {
    $result = $conn->query("SELECT UA_ID FROM USER_ADDRESS WHERE U_ID = $userId ORDER BY UA_ID DESC LIMIT 1");
    $row = $result->fetch_assoc();
    $addressId = $row['UA_ID'] ?? null;
}

if (!$addressId) {
    http_response_code(400);
    echo json_encode(['error' => 'No address available for this order.']);
    exit;
}

// ✅ Step 2: Insert order
$orderStatus = 1;
$stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, AD_ID, OS_ID, O_TotalAmount, O_DC) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiidd", $userId, $addressId, $orderStatus, $total, $discount);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to save order']);
    exit;
}
$orderId = $stmt->insert_id;
$stmt->close();

// ✅ Step 3: Insert order items
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
