<?php
session_start();
require 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['U_Email'];
    $phone = $_POST['U_PNumber'];

    try {
        // 验证邮箱和电话是否匹配
        $stmt = $conn->prepare("SELECT U_ID FROM USER WHERE U_Email = ? AND U_PNumber = ?");
        $stmt->execute([$email, $phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 生成一次性令牌（有效1小时）
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 3600);
            
            // 存储令牌到数据库
            $updateStmt = $conn->prepare("UPDATE USER SET reset_token = ?, reset_expires = ? WHERE U_ID = ?");
            $updateStmt->execute([$token, $expires, $user['U_ID']]);
            
            // 跳转到密码重置页面
            header("Location: reset_password.php?token=$token");
            exit();
        } else {
            echo "Invalid email or phone number.";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>