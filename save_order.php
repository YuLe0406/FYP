<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';
require 'send_order_email.php'; // 包含邮件发送函数

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

// 验证支付方式
$validMethods = ['Credit Card', 'PayPal'];
$paymentMethod = $data['payment_method'] ?? 'Credit Card';
if (!in_array($paymentMethod, $validMethods)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payment method']);
    exit;
}

// 验证地址
$addressId = $data['address_id'] ?? null;
if (empty($addressId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please select a shipping address']);
    exit;
}

// 验证地址是否属于该用户
$stmt = $conn->prepare("SELECT 1 FROM USER_ADDRESS WHERE UA_ID = ? AND U_ID = ?");
$stmt->bind_param("ii", $addressId, $userId);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid shipping address']);
    exit;
}
$stmt->close();

// 处理支付详情
if ($paymentMethod === 'Credit Card') {
    $cardNumber = $data['cardNumber'] ?? '';
    $expiryDate = $data['expiryDate'] ?? '';
    $cvv = $data['cvv'] ?? '';
    
    if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
        http_response_code(400);
        echo json_encode(['error' => 'Card details required for credit card payment']);
        exit;
    }
    
    $cardNumber = preg_replace('/\s+/', '', $cardNumber);
    if (!preg_match('/^[0-9]{12,19}$/', $cardNumber)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid card number']);
        exit;
    }
    
    if (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $expiryDate)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid expiry date (MM/YY format required)']);
        exit;
    }
    
    if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid CVV (3 or 4 digits required)']);
        exit;
    }
} else {
    $cardNumber = 'N/A';
    $expiryDate = 'N/A';
    $cvv = 'N/A';
}

// 计算总金额
$total = 0;
foreach ($cart as $item) {
    if (!isset($item['price']) || !isset($item['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid cart item structure']);
        exit;
    }
    $total += $item['price'] * $item['quantity'];
}

// 开始事务
$conn->begin_transaction();

try {
    // 步骤1: 保存订单
    $orderStatus = 1; // 待处理
    $stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, UA_ID, OS_ID, O_TotalAmount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $userId, $addressId, $orderStatus, $total);
    if (!$stmt->execute()) {
        throw new Exception('Failed to save order');
    }
    $orderId = $stmt->insert_id;
    $stmt->close();

    // 步骤2: 保存支付信息
    $stmt = $conn->prepare("INSERT INTO PAYMENT (O_ID, Pay_Method, Pay_Amount, Pay_CardNumber, Pay_ExpiryDate, Pay_CVV) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsss", $orderId, $paymentMethod, $total, $cardNumber, $expiryDate, $cvv);
    if (!$stmt->execute()) {
        throw new Exception('Failed to save payment');
    }
    $stmt->close();

    // 步骤3: 保存订单商品并减少库存
    $stmt = $conn->prepare("INSERT INTO ORDER_ITEMS (O_ID, P_ID, PV_ID, OI_Quantity, OI_Price) VALUES (?, ?, ?, ?, ?)");
    $updateStock = $conn->prepare("UPDATE PRODUCT_VARIANTS SET P_Quantity = P_Quantity - ? WHERE PV_ID = ?");
    
    foreach ($cart as $item) {
        $pId = $item['id'];
        $pvId = $item['variantId'] ?? null;
        $qty = $item['quantity'];
        $price = $item['price'];

        $stmt->bind_param("iiiid", $orderId, $pId, $pvId, $qty, $price);
        if (!$stmt->execute()) {
            throw new Exception('Failed to save order item');
        }

        if ($pvId) {
            $updateStock->bind_param("ii", $qty, $pvId);
            if (!$updateStock->execute()) {
                throw new Exception('Failed to update stock');
            }
        }
    }
    
    $stmt->close();
    $updateStock->close();
    
    // 提交事务
    $conn->commit();
    
    // 发送确认邮件
    $email = $data['email'] ?? '';
    $fullName = $data['fullname'] ?? '';
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $emailSent = sendOrderConfirmationEmail(
                $email,
                $orderId,
                $total,
                $cart,
                $paymentMethod,
                $fullName
            );
            
            if (!$emailSent) {
                error_log("Failed to send order confirmation email for order #$orderId");
            }
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
        }
    }
    
    echo json_encode(['success' => true, 'order_id' => $orderId]);
    
} catch (Exception $e) {
    // 回滚事务
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>