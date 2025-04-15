<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // 1. 验证密码一致性
    if ($newPassword !== $confirmPassword) {
        die("错误：两次输入的密码不一致");
    }

    // 2. 验证令牌有效性
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // 3. 更新密码
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE U_Email = ?");
        $updateStmt->bind_param("ss", $hash, $user['email']);
        $updateStmt->execute();

        // 4. 清除会话
        session_unset();
        session_destroy();
        session_write_close();

        // 5. 提示用户
        echo '<script>
            alert("密码更新成功！");
            window.location.href = "login.html";
        </script>';
        exit();
    } else {
        die("Invalid or expired reset tokens");
    }
}
?>
