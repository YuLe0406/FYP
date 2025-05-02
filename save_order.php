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

// Validate payment method
$validMethods = ['Credit Card', 'PayPal'];
$paymentMethod = $data['payment_method'] ?? 'Credit Card';
if (!in_array($paymentMethod, $validMethods)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payment method']);
    exit;
}

// Validate address
$addressId = $data['address_id'] ?? null;
if (empty($addressId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please select a shipping address']);
    exit;
}

// Verify the address belongs to the user
$stmt = $conn->prepare("SELECT 1 FROM USER_ADDRESS WHERE UA_ID = ? AND U_ID = ?");
$stmt->bind_param("ii", $addressId, $userId);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid shipping address']);
    exit;
}
$stmt->close();

// Handle payment details based on payment method
if ($paymentMethod === 'Credit Card') {
    $cardNumber = $data['cardNumber'] ?? '';
    $expiryDate = $data['expiryDate'] ?? '';
    $cvv = $data['cvv'] ?? '';
    
    if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
        http_response_code(400);
        echo json_encode(['error' => 'Card details required for credit card payment']);
        exit;
    }
    
    // Basic card number validation
    $cardNumber = preg_replace('/\s+/', '', $cardNumber);
    if (!preg_match('/^[0-9]{12,19}$/', $cardNumber)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid card number']);
        exit;
    }
    
    // Basic expiry date validation (MM/YY format)
    if (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $expiryDate)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid expiry date (MM/YY format required)']);
        exit;
    }
    
    // Basic CVV validation
    if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid CVV (3 or 4 digits required)']);
        exit;
    }
} else {
    // PayPal payment - no card details needed
    $cardNumber = 'N/A';
    $expiryDate = 'N/A';
    $cvv = 'N/A';
}

// Calculate total
$total = 0;
foreach ($cart as $item) {
    if (!isset($item['price']) || !isset($item['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid cart item structure']);
        exit;
    }
    $total += $item['price'] * $item['quantity'];
}

// Start transaction
$conn->begin_transaction();

try {
    // Step 1: Save order with address reference
    $orderStatus = 1; // Pending
    $stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, UA_ID, OS_ID, O_TotalAmount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $userId, $addressId, $orderStatus, $total);
    if (!$stmt->execute()) {
        throw new Exception('Failed to save order');
    }
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Step 2: Save payment
    $stmt = $conn->prepare("INSERT INTO PAYMENT (O_ID, Pay_Method, Pay_Amount, Pay_CardNumber, Pay_ExpiryDate, Pay_CVV) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsss", $orderId, $paymentMethod, $total, $cardNumber, $expiryDate, $cvv);
    if (!$stmt->execute()) {
        throw new Exception('Failed to save payment');
    }
    $stmt->close();

    // Step 3: Save order items & deduct stock
    $stmt = $conn->prepare("INSERT INTO ORDER_ITEMS (O_ID, P_ID, PV_ID, OI_Quantity, OI_Price) VALUES (?, ?, ?, ?, ?)");
    $updateStock = $conn->prepare("UPDATE PRODUCT_VARIANTS SET P_Quantity = P_Quantity - ? WHERE PV_ID = ?");
    
    foreach ($cart as $item) {
        $pId = $item['id'];
        $pvId = $item['variantId'] ?? null;
        $qty = $item['quantity'];
        $price = $item['price'];

        // Save order item
        $stmt->bind_param("iiiid", $orderId, $pId, $pvId, $qty, $price);
        if (!$stmt->execute()) {
            throw new Exception('Failed to save order item');
        }

        // Decrease stock if variant exists
        if ($pvId) {
            $updateStock->bind_param("ii", $qty, $pvId);
            if (!$updateStock->execute()) {
                throw new Exception('Failed to update stock');
            }
        }
    }
    
    $stmt->close();
    $updateStock->close();
    
    // Commit transaction if all operations succeeded
    $conn->commit();
    
    echo json_encode(['success' => true, 'order_id' => $orderId]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>