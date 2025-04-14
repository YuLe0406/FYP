<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productId'])) {
    $productId = intval($_POST['productId']);

    if (!empty($_FILES['newImages']['name'][0])) {
        $uploadDir = 'uploads/'; // Make sure this folder exists and is writable

        foreach ($_FILES['newImages']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['newImages']['name'][$key]);
            $targetFile = $uploadDir . time() . '_' . $fileName;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES (?, ?)");
                $stmt->bind_param("is", $productId, $targetFile);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

header("Location: edit_product.php?productId=$productId");
exit;
