<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$productId = intval($_POST['productId']);
$size = trim($_POST['size']);
$quantity = intval($_POST['quantity']);

$stmt = $conn->prepare("INSERT INTO PRODUCT_VARIANTS (P_ID, P_Size, P_Quantity) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $productId, $size, $quantity);

if ($stmt->execute()) {
    header("Location: edit_product.php?productId=$productId&success=Variant+added+successfully");
} else {
    die("Error adding variant: " . $conn->error);
}
$stmt->close();
?>