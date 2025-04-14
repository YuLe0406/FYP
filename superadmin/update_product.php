<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productId = intval($_POST['productId']);
    $categoryID = intval($_POST['productCategory']);
    $productName = trim($_POST['productName']);
    $productPrice = floatval($_POST['productPrice']);

    if ($productPrice < 0) {
        die("Error: Price cannot be negative.");
    }

    $stmt = $conn->prepare("UPDATE PRODUCT SET C_ID = ?, P_Name = ?, P_Price = ? WHERE P_ID = ?");
    $stmt->bind_param("isdi", $categoryID, $productName, $productPrice, $productId);
    
    if ($stmt->execute()) {
        header("Location: edit_product.php?productId=" . $productId . "&updated=1");
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }

    $stmt->close();
}
?>
