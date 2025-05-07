<?php
include 'db.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = intval($_POST['productId']);
        
        if (empty($_FILES['newPrimaryImage']['name'])) {
            throw new Exception("No image selected for upload");
        }

        $imageDir = '../images/';
        $primaryImage = $_FILES['newPrimaryImage'];
        
        // Validate file type
        $fileType = strtolower(pathinfo($primaryImage['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG, and GIF files are allowed");
        }

        // Get current image path
        $currentImageQuery = "SELECT P_Picture FROM PRODUCT WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $currentImageQuery);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $currentImagePath);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Generate unique filename
        $primaryImageName = uniqid() . '_' . basename($primaryImage['name']);
        $newImagePath = 'images/' . $primaryImageName;
        $targetPath = $imageDir . $primaryImageName;

        // Move uploaded file
        if (!move_uploaded_file($primaryImage['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload new primary image");
        }

        // Update database
        $updateQuery = "UPDATE PRODUCT SET P_Picture = ? WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "si", $newImagePath, $productId);
        
        if (!mysqli_stmt_execute($stmt)) {
            // Delete the new image if update fails
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            throw new Exception("Failed to update primary image: " . mysqli_error($conn));
        }
        
        mysqli_stmt_close($stmt);

        // Delete old image if it exists and is different
        if (!empty($currentImagePath) && $currentImagePath !== $newImagePath && file_exists('../' . $currentImagePath)) {
            unlink('../' . $currentImagePath);
        }

        // Redirect with success message
        header("Location: edit_product.php?productId=$productId&success=Primary image updated successfully");
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