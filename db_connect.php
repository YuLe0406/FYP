<?php
$servername = "localhost";
$username = "root";  // Change if using a different username
$password = "";      // Change if there's a password
$database = "ctrlx";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set UTF-8 encoding
$conn->set_charset("utf8");
?>
