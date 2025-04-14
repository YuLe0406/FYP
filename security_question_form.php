<?php
session_start();
require __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower($_POST['email'] ?? '');
    
    try {
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