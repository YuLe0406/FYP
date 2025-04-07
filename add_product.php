<?php
include 'db.php';

$category = $_POST['productCategory'];
$name = $_POST['productName'];
$price = $_POST['productPrice'];

$imageName = $_FILES['productImage']['name'];
$tmpName = $_FILES['productImage']['tmp_name'];
$uploadDir = 'uploads/';
$uploadPath = $uploadDir . basename($imageName);

move_uploaded_file($tmpName, $uploadPath);

$sql = "INSERT INTO PRODUCT (C_ID, P_Name, P_Price, P_Picture)
        VALUES ('$category', '$name', '$price', '$uploadPath')";
mysqli_query($conn, $sql);

header("Location: product.php");
?>
