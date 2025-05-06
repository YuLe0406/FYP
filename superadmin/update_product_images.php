<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['productId'];
    
    try {
        // Handle new image uploads
        if (!empty($_FILES['newImages']['name'][0])) {
            $uploadDir = '../FYP/images/';
            
            foreach ($_FILES['newImages']['tmp_name'] as $key => $tmpName) {
                $fileName = basename($_FILES['newImages']['name'][$key]);
                $uploadPath = $uploadDir . uniqid() . '_' . $fileName;
                
                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $relativePath = 'FYP/' . str_replace('../FYP/', '', $uploadPath);
                    $imgStmt = $conn->prepare("INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES (?, ?)");
                    $imgStmt->bind_param("is", $productId, $relativePath);
                    $imgStmt->execute();
                    $imgStmt->close();
                }
            }
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