<?php
session_start();
require 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['U_Email'];
    
    try {
       // 检查类似查询
        $stmt = $conn->prepare("SELECT security_question FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['attempts'] = 0;
            header("Location: security_question_form.php");
            exit();
        } else {
            echo "If an account exists with this email, you'll receive security instructions.";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>