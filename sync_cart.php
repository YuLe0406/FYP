<?php
include 'db.php';
session_start();

if (!isset($_SESSION['U_ID'])) {
    http_response_code(403);
    echo "User not logged in.";
    exit;
}

$U_ID = $_SESSION['U_ID'];
$data = json_decode(file_get_contents("php://input"), true);

foreach ($data as $item) {
    $P_ID = intval($item['id']);
    $P_Size = $item['size'];
    $Quantity = intval($item['quantity']);

    // Insert or update quantity
    $sql = "INSERT INTO CART (U_ID, P_ID, P_Size, CART_Quantity)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE CART_Quantity = CART_Quantity + VALUES(CART_Quantity)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $U_ID, $P_ID, $P_Size, $Quantity);
    $stmt->execute();
}

echo "Cart synced.";
?>
