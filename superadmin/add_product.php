<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = $_POST['productCategory'];
    $productName = $_POST['productName'];
    $productPrice = $_POST['productPrice'];
    $productDescription = $_POST['productDescription'];
    
    try {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        // Insert product
        $stmt = $conn->prepare("INSERT INTO PRODUCT (C_ID, P_Name, P_Price, P_DES) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $categoryId, $productName, $productPrice, $productDescription);
        $stmt->execute();
        $productId = $stmt->insert_id;
        $stmt->close();
        
        // Handle image uploads
        if (!empty($_FILES['productImages']['name'][0])) {
            $uploadDir = '../FYP/images/';
            
            foreach ($_FILES['productImages']['tmp_name'] as $key => $tmpName) {
                $fileName = basename($_FILES['productImages']['name'][$key]);
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
        
        // Commit transaction
        mysqli_commit($conn);
        
        header("Location: product.php?success=1");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: product.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: product.php");
    exit();
}
?>