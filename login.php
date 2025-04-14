<?php
session_start();
require __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = strtolower($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';


    $errors = [];
    if (empty(trim($email)))    $errors[] = "Email is required";
    if (empty(trim($password))) $errors[] = "Password is required";

    if (!empty($errors)) {
        die("Login failed:<br>" . implode("<br>", $errors));
    }

    try {
       
        $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            die("Invalid email or password!");
        }
    } catch (PDOException $e) {
        die("Login failed: " . $e->getMessage());
    }
}
?>