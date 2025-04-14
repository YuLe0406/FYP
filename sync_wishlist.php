<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$user_id = $_SESSION['user_id'];

foreach ($data as $item) {
    $p_id = intval($item['id']);
    $size = $item['size'];

    // Insert if not exists
    $sql = "INSERT IGNORE INTO wishlist (U_ID, P_ID, P_Size) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $p_id, $size);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['status' => 'success']);
?>
