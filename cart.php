<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']);
    $size = $_POST['size'];
    $quantity = intval($_POST['quantity']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Change to actual user session ID

    // ✅ Check if Product Variant exists
    $sql_pv = "SELECT PV_ID FROM product_variants WHERE P_ID = ? AND P_Size = ?";
    $stmt = $conn->prepare($sql_pv);
    $stmt->bind_param("is", $product_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("❌ No matching variant found for P_ID: $product_id and Size: $size <br>");
    }

    $row = $result->fetch_assoc();
    $pv_id = $row['PV_ID'];
    echo "✅ Found PV_ID: $pv_id <br>";

    // ✅ Insert into Cart
    $insert_sql = "INSERT INTO cart (user_id, product_id, pv_id, quantity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iiii", $user_id, $product_id, $pv_id, $quantity);

    if ($stmt->execute()) {
        echo "🎉 Product added to cart!";
    } else {
        echo "❌ Insert Error: " . $stmt->error;
    }
}
?>
