<?php
include 'db.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create the images directory if it doesn't exist
$imageDir = '../images/';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0777, true);
}

// Process form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $categoryId = intval($_POST['productCategory']);
        $productName = mysqli_real_escape_string($conn, $_POST['productName']);
        $productPrice = floatval($_POST['productPrice']);
        $productDescription = mysqli_real_escape_string($conn, $_POST['productDescription']);

        // Handle primary image upload (required)
        if (empty($_FILES['primaryImage']['name'])) {
            throw new Exception("Primary image is required");
        }

        $primaryImage = $_FILES['primaryImage'];
        $primaryImageName = uniqid() . '_' . basename($primaryImage['name']);
        $primaryImagePath = 'images/' . $primaryImageName;
        $targetPath = $imageDir . $primaryImageName;
        
        if (!move_uploaded_file($primaryImage['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload primary image");
        }

        // Insert product into database
        $insertProduct = "INSERT INTO PRODUCT (C_ID, P_Name, P_Price, P_Picture, P_DES, P_Status) 
                          VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = mysqli_prepare($conn, $insertProduct);
        mysqli_stmt_bind_param($stmt, "isdss", $categoryId, $productName, $productPrice, $primaryImagePath, $productDescription);
        
        if (!mysqli_stmt_execute($stmt)) {
            // Delete uploaded file if database insert fails
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            throw new Exception("Failed to add product: " . mysqli_error($conn));
        }
        
        $productId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Redirect with success message
        header("Location: product.php?success=Product added successfully");
        exit();
    } catch (Exception $e) {
        // Delete any uploaded files if an error occurred
        if (!empty($primaryImagePath) && file_exists($imageDir . basename($primaryImagePath))) {
            unlink($imageDir . basename($primaryImagePath));
        }
        
        // Redirect with error message
        header("Location: product.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: product.php");
    exit();
}