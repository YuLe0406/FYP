<?php
include 'db.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = intval($_POST['productId']);
        $categoryId = intval($_POST['productCategory']);
        $productName = mysqli_real_escape_string($conn, $_POST['productName']);
        $productPrice = floatval($_POST['productPrice']);
        $productDescription = mysqli_real_escape_string($conn, $_POST['productDescription']);

        // Get current primary image path
        $currentImageQuery = "SELECT P_Picture FROM PRODUCT WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $currentImageQuery);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $currentImagePath);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $newImagePath = $currentImagePath;
        $imageDir = '../images/';

        // Handle new primary image upload
        if (!empty($_FILES['newPrimaryImage']['name'])) {
            $primaryImage = $_FILES['newPrimaryImage'];
            $primaryImageName = uniqid() . '_' . basename($primaryImage['name']);
            $newImagePath = 'images/' . $primaryImageName;
            $targetPath = $imageDir . $primaryImageName;
            
            if (move_uploaded_file($primaryImage['tmp_name'], $targetPath)) {
                // Delete old image if it exists and is different
                if (!empty($currentImagePath) && $currentImagePath !== $newImagePath && file_exists('../' . $currentImagePath)) {
                    unlink('../' . $currentImagePath);
                }
            } else {
                throw new Exception("Failed to upload new primary image");
            }
        }

        // Update product in database
        $updateQuery = "UPDATE PRODUCT SET C_ID = ?, P_Name = ?, P_Price = ?, P_Picture = ?, P_DES = ? WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "isdssi", $categoryId, $productName, $productPrice, $newImagePath, $productDescription, $productId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update product: " . mysqli_error($conn));
        }
        
        mysqli_stmt_close($stmt);

        // Redirect with success message
        header("Location: edit_product.php?productId=$productId&success=Product updated successfully");
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