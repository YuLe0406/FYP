<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variantId = intval($_POST['variantId']);
    $productId = intval($_POST['productId']);
    $size = trim($_POST['size']);
    $quantity = max(0, intval($_POST['quantity'])); // prevent negative
    $colorId = intval($_POST['colorId']);

    $stmt = $conn->prepare("UPDATE PRODUCT_VARIANTS SET P_Size = ?, P_Quantity = ?, PC_ID = ? WHERE PV_ID = ?");
    $stmt->bind_param("siii", $size, $quantity, $colorId, $variantId);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_product.php?productId=$productId");
    exit;
}
?>
