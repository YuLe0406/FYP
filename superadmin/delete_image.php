<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['imageId'], $_POST['productId'])) {
    $imageId = intval($_POST['imageId']);
    $productId = intval($_POST['productId']);

    $stmt = $conn->prepare("SELECT PRODUCT_IMAGE FROM PRODUCT_IMAGES WHERE PI_ID = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $img = $result->fetch_assoc();
    $stmt->close();

    if ($img && file_exists($img['PRODUCT_IMAGE'])) {
        unlink($img['PRODUCT_IMAGE']);
    }

    $delStmt = $conn->prepare("DELETE FROM PRODUCT_IMAGES WHERE PI_ID = ?");
    $delStmt->bind_param("i", $imageId);
    $delStmt->execute();
    $delStmt->close();

    header("Location: edit_product.php?productId=$productId");
    exit;
}
?>
