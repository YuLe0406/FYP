<?php
header("Content-Type: application/json"); // Force JSON response

// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

// ✅ Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// ✅ Get order data from JavaScript
$data = json_decode(file_get_contents("php://input"), true);

// Check if data is valid
if (!$data || !isset($data['cart']) || !is_array($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order data']);
    exit;
}

$userId = $_SESSION['user_id'];
$cart = $data['cart'];
$paymentMethod = $data['payment_method'] ?? 'cod'; // Not really used now but later can
$discount = $data['discount'] ?? 0;

// ✅ Calculate total
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// ✅ Get user's saved address (optional, for your own reference)
$stmt = $conn->prepare("SELECT U_Address FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || empty($user['U_Address'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No saved address found. Please update profile address.']);
    exit;
}

// ✅ Insert into ORDERS table
$orderStatus = 1; // Pending
$stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, AD_ID, OS_ID, O_TotalAmount, O_DC) VALUES (?, NULL, ?, ?, ?)");
$stmt->bind_param("iidd", $userId, $orderStatus, $total, $discount);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to save order']);
    exit;
}
$orderId = $stmt->insert_id;
$stmt->close();

// ✅ Insert into ORDER_ITEMS table
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

// ✅ Final success
echo json_encode(['success' => true, 'order_id' => $orderId]);
?>
