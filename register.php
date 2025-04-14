<?php
require __DIR__ . '/includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据
    $firstName = $_POST['U_FName'];
    $lastName = $_POST['U_LName'];
    $dob = $_POST['U_DOB'];
    $gender = $_POST['U_Gender'];
    $email = $_POST['U_Email'];
    $password = password_hash($_POST['U_Password'], PASSWORD_DEFAULT);
    $phone = $_POST['U_PNumber'];

    try {
        // 插入用户数据
        $stmt = $conn->prepare("INSERT INTO USER (U_FName, U_LName, U_DOB, U_Gender, U_Email, U_Password, U_PNumber) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $dob, $gender, $email, $password, $phone]);
        
        echo "注册成功！<a href='login.html'>立即登录</a>";
    } catch (PDOException $e) {
        die("注册失败：" . $e->getMessage());
    }
}
?>