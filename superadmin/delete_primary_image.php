<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = intval($_POST['productId']);
        
        // Get current image path
        $currentImageQuery = "SELECT P_Picture FROM PRODUCT WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $currentImageQuery);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $currentImagePath);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (empty($currentImagePath)) {
            throw new Exception("No primary image found to delete");
        }

        // Update database to remove primary image
        $updateQuery = "UPDATE PRODUCT SET P_Picture = NULL WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to remove primary image: " . mysqli_error($conn));
        }
        
        mysqli_stmt_close($stmt);

        // Delete the image file
        $fullPath = '../' . ltrim($currentImagePath, 'FYP/');
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Redirect with success message
        header("Location: edit_product.php?productId=$productId&success=Primary image deleted successfully");
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