<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $categoryID = intval($_POST['productCategory']);
    $productName = trim($_POST['productName']);
    $productPrice = floatval($_POST['productPrice']);

    // Validate inputs
    if ($productPrice < 0) {
        die("Error: Product price cannot be negative.");
    }

    if (empty($productName) || $categoryID <= 0) {
        die("Error: Invalid product name or category.");
    }

    // Insert into PRODUCT table
    $stmt = $conn->prepare("INSERT INTO PRODUCT (C_ID, P_Name, P_Price) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $categoryID, $productName, $productPrice);

    if ($stmt->execute()) {
        $productID = $stmt->insert_id; // Get the new product ID

        // Handle image uploads
        if (isset($_FILES['productImages']) && count($_FILES['productImages']['name']) > 0) {
            $uploadDir = "uploads/";

            // Make sure upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['productImages']['tmp_name'] as $index => $tmpName) {
                $originalName = basename($_FILES['productImages']['name'][$index]);
                $imageType = mime_content_type($tmpName);

                // Check if file is an image
                if (strpos($imageType, "image") === false) {
                    continue; // Skip non-images
                }

                $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);
                $uniqueName = uniqid("img_", true) . "." . $fileExt;
                $targetPath = $uploadDir . $uniqueName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Save image path in DB
                    $imgStmt = $conn->prepare("INSERT INTO PRODUCT_IMAGES (P_ID, PRODUCT_IMAGE) VALUES (?, ?)");
                    $imgStmt->bind_param("is", $productID, $targetPath);
                    $imgStmt->execute();
                    $imgStmt->close();
                }
            }
        }

        // Success
        header("Location: product.php?success=1");
        exit();
    } else {
        die("Error inserting product: " . $stmt->error);
    }
    $stmt->close();
} else {
    echo "Invalid request method.";
}
?>
