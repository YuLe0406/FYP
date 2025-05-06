<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['productId'];
    $categoryId = $_POST['productCategory'];
    $productName = $_POST['productName'];
    $productPrice = $_POST['productPrice'];
    
    try {
        $stmt = $conn->prepare("UPDATE PRODUCT SET C_ID = ?, P_Name = ?, P_Price = ? WHERE P_ID = ?");
        $stmt->bind_param("isdi", $categoryId, $productName, $productPrice, $productId);
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