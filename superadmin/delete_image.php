<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['productId'];
    $imageId = $_POST['imageId'];
    
    try {
        // Get image path first
        $stmt = $conn->prepare("SELECT PRODUCT_IMAGE FROM PRODUCT_IMAGES WHERE PI_ID = ? AND P_ID = ?");
        $stmt->bind_param("ii", $imageId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        $stmt->close();
        
        if ($image) {
            // Delete from filesystem
            $filePath = '../' . ltrim($image['PRODUCT_IMAGE'], 'FYP/');
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete from database
            $stmt = $conn->prepare("DELETE FROM PRODUCT_IMAGES WHERE PI_ID = ?");
            $stmt->bind_param("i", $imageId);
            $stmt->execute();
            $stmt->close();
        }
        
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