<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variantId = $_POST['variantId'];
    $productId = $_POST['productId'];
    $colorId = $_POST['colorId'];
    $size = $_POST['size'];
    $quantity = $_POST['quantity'];
    
    try {
        $stmt = $conn->prepare("UPDATE PRODUCT_VARIANTS SET PC_ID = ?, P_Size = ?, P_Quantity = ? WHERE PV_ID = ?");
        $stmt->bind_param("isii", $colorId, $size, $quantity, $variantId);
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