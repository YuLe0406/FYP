<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['productId']);
    $colorId = intval($_POST['colorId']);
    $size = trim($_POST['size']);
    $quantity = max(0, intval($_POST['quantity'])); // prevent negative

    $stmt = $conn->prepare("INSERT INTO PRODUCT_VARIANTS (P_ID, PC_ID, P_Size, P_Quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $productId, $colorId, $size, $quantity);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_product.php?productId=$productId");
    exit;
}
?>
