<?php
require 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // 验证密码一致性
    if ($newPassword !== $confirmPassword) {
        die("Passwords do not match.");
    }

    try {
        // 验证令牌有效性
        $stmt = $conn->prepare("SELECT U_ID FROM USER WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 更新密码并清除令牌
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE USER SET U_Password = ?, reset_token = NULL, reset_expires = NULL WHERE U_ID = ?");
            $updateStmt->execute([$hash, $user['U_ID']]);
            
            echo "Password updated successfully. <a href='login.html'>Login here</a>";
        } else {
            die("Invalid or expired token.");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>