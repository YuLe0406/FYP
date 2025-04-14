<?php
include 'db.php';

header('Content-Type: application/json');

if (isset($_GET['productId'])) {
    $productId = (int)$_GET['productId'];
    $query = "SELECT * FROM PRODUCT_IMAGES WHERE P_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    echo json_encode($images);
} else {
    echo json_encode([]);
}
?>