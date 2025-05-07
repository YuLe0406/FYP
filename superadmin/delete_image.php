<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = intval($_POST['productId']);
        $imageId = intval($_POST['imageId']);
        
        // Get image path before deleting
        $getImageQuery = "SELECT PRODUCT_IMAGE FROM PRODUCT_IMAGES WHERE PI_ID = ? AND P_ID = ?";
        $stmt = mysqli_prepare($conn, $getImageQuery);
        mysqli_stmt_bind_param($stmt, "ii", $imageId, $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $imagePath);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if (empty($imagePath)) {
            throw new Exception("Image not found or doesn't belong to this product");
        }
        
        // Delete from database
        $deleteQuery = "DELETE FROM PRODUCT_IMAGES WHERE PI_ID = ? AND P_ID = ?";
        $stmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "ii", $imageId, $productId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$success) {
            throw new Exception("Failed to delete image from database");
        }
        
        // Delete file if it exists
        $fullPath = '../' . ltrim($imagePath, 'FYP/');
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Redirect with success message
        header("Location: edit_product.php?productId=$productId&success=Image deleted successfully");
        exit();
    } catch (Exception $e) {
        // Redirect with error message
        header("Location: edit_product.php?productId=$productId&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: product.php");
    exit();
}