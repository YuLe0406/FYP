<?php
$db_host = "localhost";
$db_name = "ctrlx_users";  // 替换为你的数据库名
$db_user = "root";
$db_pass = "";

try {
    $conn = new PDO(
        "mysql:host=$db_host;dbname=$db_name",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>