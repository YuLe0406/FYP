<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['id']);
    $size = $_POST['size'];
    $quantity = intval($_POST['quantity']);
    $user_id = 1; // Change this to actual logged-in user ID

    // Check if the product already exists in the cart
    $check_sql = "SELECT * FROM CART WHERE user_id = ? AND product_id = ? AND size = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("iis", $user_id, $product_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if the product already exists
        $update_sql = "UPDATE CART SET quantity = quantity + ? WHERE user_id = ? AND product_id = ? AND size = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iiis", $quantity, $user_id, $product_id, $size);
        $stmt->execute();
    } else {
        // Insert new item if it doesn't exist
        $insert_sql = "INSERT INTO CART (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iisi", $user_id, $product_id, $size, $quantity);
        $stmt->execute();
    }

    echo "Added to Cart!";
    $stmt->close();
    $conn->close();
}
?>
