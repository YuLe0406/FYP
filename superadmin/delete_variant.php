<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['variantId'], $_POST['productId'])) {
    $variantId = intval($_POST['variantId']);
    $productId = intval($_POST['productId']);

    $stmt = $conn->prepare("DELETE FROM PRODUCT_VARIANTS WHERE PV_ID = ?");
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_product.php?productId=$productId");
    exit;
}
?>
