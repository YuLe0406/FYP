<?php
session_start();
require 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['U_Email'];
    $password = $_POST['U_Password'];

    try {
        
       // 正确表名 'users'
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['U_Password'])) {
           
            $_SESSION['user_id'] = $user['U_ID'];
            $_SESSION['user_email'] = $user['U_Email'];
            $_SESSION['user_name'] = $user['U_FName'] . ' ' . $user['U_LName'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid email or password!";
        }
    } catch (PDOException $e) {
        die("Login failed: " . $e->getMessage());
    }
}
?>