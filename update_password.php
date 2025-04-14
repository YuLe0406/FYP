<?php
// 必须在脚本最开头启动会话
session_start();
require 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // 1. 验证密码一致性
    if ($newPassword !== $confirmPassword) {
        die("错误：两次输入的密码不一致");
    }

    try {
        // 2. 验证令牌有效性
        $stmt = $conn->prepare("SELECT U_Email FROM USER WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 3. 更新密码
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE USER SET U_Password = ?, reset_token = NULL, reset_expires = NULL WHERE U_Email = ?");
            $updateStmt->execute([$hash, $user['U_Email']]);
            
            // 4. 安全清除会话
            session_unset();    // 清除所有会话变量
            session_destroy(); // 销毁会话
            session_write_close(); // 确保会话数据写入文件
            
            // 5. 跳转与提示优化
            echo '<script>
                alert("密码更新成功！");
                window.location.href = "login.html";
            </script>';
            exit();
        } else {
            die("错误：无效或过期的重置令牌");
        }
    } catch (PDOException $e) {
        error_log("密码更新错误: " . $e->getMessage());
        die("系统错误，请联系管理员");
    }
}
?>