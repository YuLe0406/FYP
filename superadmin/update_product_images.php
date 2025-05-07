<?php
include 'db.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = intval($_POST['productId']);
        
        // Check if any files were uploaded
        if (empty($_FILES['newImages']['name'][0])) {
            throw new Exception("No images selected for upload");
        }

        // Create the images directory if it doesn't exist
        $imageDir = '../images/';
        if (!file_exists($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        // Process each uploaded file
        $uploadedFiles = $_FILES['newImages'];
        $successCount = 0;
        
        foreach ($uploadedFiles['name'] as $key => $name) {
            // Skip if there was an upload error
            if ($uploadedFiles['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Validate file type
            $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($fileType, $allowedTypes)) {
                continue;
            }

            // Generate unique filename
            $imageName = uniqid() . '_' . basename($name);
            $imagePath = 'images/' . $imageName;
            $targetPath = $imageDir . $imageName;

            // Move uploaded file
            if (move_uploaded_file($uploadedFiles['tmp_name'][$key], $targetPath)) {
                // Insert into database
                $insertImage = "INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $insertImage);
                mysqli_stmt_bind_param($stmt, "is", $productId, $imagePath);
                
                if (mysqli_stmt_execute($stmt)) {
                    $successCount++;
                }
                
                mysqli_stmt_close($stmt);
            }
        }

        if ($successCount === 0) {
            throw new Exception("Failed to upload any images. Please check file types (JPG, PNG, GIF) and try again.");
        }

        // Redirect with success message
        header("Location: edit_product.php?productId=$productId&success=Successfully uploaded $successCount image(s)");
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