<?php
session_start();
require __DIR__ . '/db.php';

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Input validation
    $errors = [];
    if (empty(trim($email)))    $errors[] = "Email is required";
    if (empty(trim($password))) $errors[] = "Password is required";

    if (!empty($errors)) {
        die("Login failed:<br>" . implode("<br>", $errors));
    }

    // Use MySQLi correctly
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die("Error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email); // Bind parameter
    $stmt->execute();
    
    $result = $stmt->get_result(); // Get result set
    $user = $result->fetch_assoc(); // Fetch associative array

    // Debug output (optional)
    echo "<pre>User Data: ";
    print_r($user);
    echo "</pre>";

    // Verify password
    if ($user && $password === $user['password_hash']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        header("Location: index.php");
        exit();
    } else {
        die("Invalid email or password!");
    }

    $stmt->close();
}
?>