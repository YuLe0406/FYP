<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['productId'];
    $colorId = $_POST['colorId'];
    $size = $_POST['size'];
    $quantity = $_POST['quantity'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO PRODUCT_VARIANTS (P_ID, PC_ID, P_Size, P_Quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $productId, $colorId, $size, $quantity);
        $stmt->execute();
        $stmt->close();
        
        header("Location: edit_product.php?productId=$productId&success=1");
        exit();
    } catch (Exception $e) {
        header("Location: edit_product.php?productId=$productId&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: product.php");
    exit();
}
?>