<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = intval($_POST['product_id']);
    $size = $_POST['size'];
    $quantity = intval($_POST['quantity']);

    if (empty($product_id) || empty($size) || $quantity <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid product details"]);
        exit;
    }

    // Check if product exists
    $sql = "SELECT * FROM PRODUCT WHERE P_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        echo json_encode(["status" => "error", "message" => "Product not found"]);
        exit;
    }

    $price = $product['P_Price'];

    // Insert into cart table
    $sql = "INSERT INTO cart (P_ID, size, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isid", $product_id, $size, $quantity, $price);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Added to cart"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add to cart"]);
    }

    $stmt->close();
    $conn->close();
}
?>
