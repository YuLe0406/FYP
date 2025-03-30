<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_id = intval($_POST['cart_id']);
    $sql = "DELETE FROM CART WHERE cart_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    echo "Removed from Cart!";
}
?>
