<?php
$host = "localhost";
$user = "root"; // Change if using another user
$password = ""; // Change if password is set
$database = "ctrlx";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
