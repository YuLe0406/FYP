<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
$paymentMethod = $data['payment_method'] ?? 'Credit Card';
$cardNumber = $data['cardNumber'] ?? '';
$expiryDate = $data['expiryDate'] ?? '';
$cvv = $data['cvv'] ?? '';

if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payment details missing']);
    exit;
}

// ✅ Calculate total
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// ✅ Step 1: Save order
$orderStatus = 1; // Pending
$stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, OS_ID, O_TotalAmount) VALUES (?, ?, ?)");
$stmt->bind_param("iid", $userId, $orderStatus, $total);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to save order']);
    exit;
}
$orderId = $stmt->insert_id;
$stmt->close();

// ✅ Step 2: Save payment
$stmt = $conn->prepare("INSERT INTO PAYMENT (O_ID, Pay_Method, Pay_Amount, Pay_CardNumber, Pay_ExpiryDate, Pay_CVV) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isdsss", $orderId, $paymentMethod, $total, $cardNumber, $expiryDate, $cvv);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to save payment']);
    exit;
}
$stmt->close();

// ✅ Step 3: Save order items & deduct stock
$stmt = $conn->prepare("INSERT INTO ORDER_ITEMS (O_ID, P_ID, PV_ID, OI_Quantity, OI_Price) VALUES (?, ?, ?, ?, ?)");
foreach ($cart as $item) {
    $pId = $item['id'];
    $pvId = $item['variantId'] ?? null;
    $qty = $item['quantity'];
    $price = $item['price'];

    $stmt->bind_param("iiiid", $orderId, $pId, $pvId, $qty, $price);
    $stmt->execute();

    // 🔥 Decrease stock in PRODUCT_VARIANTS
    if ($pvId) {
        $updateStock = $conn->prepare("UPDATE PRODUCT_VARIANTS SET P_Quantity = P_Quantity - ? WHERE PV_ID = ?");
        $updateStock->bind_param("ii", $qty, $pvId);
        $updateStock->execute();
        $updateStock->close();
    }
}
$stmt->close();

echo json_encode(['success' => true, 'order_id' => $orderId]);
?>
