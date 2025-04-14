<?php
session_start();
require 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['U_Email'];
    
    try {
        $stmt = $conn->prepare("SELECT U_SecurityQuestion FROM USER WHERE U_Email = ?");
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