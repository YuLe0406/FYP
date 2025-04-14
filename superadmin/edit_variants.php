<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variantId = intval($_POST['variantId']);
    $colorId = intval($_POST['colorId']);
    $size = $_POST['size'];
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("UPDATE PRODUCT_VARIANTS SET PC_ID = ?, P_Size = ?, P_Quantity = ? WHERE PV_ID = ?");
    $stmt->bind_param("isii", $colorId, $size, $quantity, $variantId);

    if ($stmt->execute()) {
        header("Location: edit_product.php?productId=" . $_POST['productId']);
        exit();
    } else {
        echo "Failed to update variant.";
    }
}
?>
