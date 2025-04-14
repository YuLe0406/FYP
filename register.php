<?php
// 在文件开头添加
require __DIR__ . '/includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];

    try {
        // 插入用户数据
        $stmt = $conn->prepare("
            INSERT INTO users 
            (first_name, last_name, email, password_hash, phone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$firstName, $lastName, $email, $password, $phone]);
        
        header("Location: login.html?registration=success");
        exit();
    } catch (PDOException $e) {
        die("注册失败: " . $e->getMessage());
    }
}
?>