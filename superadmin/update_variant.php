<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$variantId = intval($_POST['variantId']);
$productId = intval($_POST['productId']);
$size = trim($_POST['size']);
$quantity = intval($_POST['quantity']);

$stmt = $conn->prepare("UPDATE PRODUCT_VARIANTS SET 
    P_Size = ?, 
    P_Quantity = ? 
    WHERE PV_ID = ?");
$stmt->bind_param("sii", $size, $quantity, $variantId);

if ($stmt->execute()) {
    header("Location: edit_product.php?productId=$productId&success=Variant+updated+successfully");
} else {
    die("Error updating variant: " . $conn->error);
}
$stmt->close();
?>