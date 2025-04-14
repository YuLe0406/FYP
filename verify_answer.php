<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['reset_email']) || $_SESSION['attempts'] > 3) {
    header("Location: forgot_password.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['attempts']++;
    
    $userAnswer = strtolower(trim($_POST['security_answer']));
    
    try {
        $stmt = $conn->prepare("SELECT U_SecurityAnswer FROM USER WHERE U_Email = ?");
        $stmt->execute([$_SESSION['reset_email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && strtolower(trim($user['U_SecurityAnswer'])) === $userAnswer) {
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 3600);
            
            $updateStmt = $conn->prepare("UPDATE USER SET reset_token = ?, reset_expires = ? WHERE U_Email = ?");
            $updateStmt->execute([$token, $expires, $_SESSION['reset_email']]);
            
            header("Location: reset_password.php?token=$token");
            exit();
        } else {
            if ($_SESSION['attempts'] >= 3) {
                session_destroy();
                die("Too many failed attempts. Please start over.");
            }
            header("Location: security_question_form.php?error=1");
            exit();
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>