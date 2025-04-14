<?php
session_start();
require __DIR__ . '/config.php';

// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 输入验证
    $errors = [];
    if (empty(trim($email)))    $errors[] = "Email is required";
    if (empty(trim($password))) $errors[] = "Password is required";

    if (!empty($errors)) {
        die("Login failed:<br>" . implode("<br>", $errors));
    }

    try {
        // 查询用户
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 调试输出
        echo "<pre>User Data: ";
        print_r($user);
        echo "</pre>";

        // 验证密码
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