<?php
session_start();
require 'db.php';

if (!isset($_SESSION['U_ID'])) {
    http_response_code(403);
    echo "Not logged in.";
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$u_id = $_SESSION['U_ID'];
$fullname = $data['fullname'];
$email = $data['email'];
$phone = $data['phone'];
$address1 = $data['address1'];
$payment = $data['payment'];
$items = $data['items']; // array of cart items

// Insert order
$stmt = $conn->prepare("INSERT INTO ORDERS (U_ID, Full_Name, Email, Phone, Address, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $u_id, $fullname, $email, $phone, $address1, $payment);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert each item into ORDER_ITEMS table
$stmt = $conn->prepare("INSERT INTO ORDER_ITEMS (Order_ID, P_ID, P_Name, Size, Quantity, Price) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($items as $item) {
    $stmt->bind_param("iissid", $order_id, $item['id'], $item['name'], $item['size'], $item['quantity'], $item['price']);
    $stmt->execute();
}

$stmt->close();
echo json_encode(['success' => true, 'order_id' => $order_id]);
?>
