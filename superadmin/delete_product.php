<?php
include 'db.php';
$productId = intval($_POST['productId']);
mysqli_query($conn, "DELETE FROM PRODUCT WHERE P_ID = $productId");
header("Location: product.php");
?>
