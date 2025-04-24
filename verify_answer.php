<?php
session_start();
require 'db.php';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['security_answer'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_answer = trim($_POST['security_answer']);
    $correct_answer = $_SESSION['security_answer'];
    
    if (strcasecmp($user_answer, $correct_answer) === 0) {
        // Correct answer, redirect to reset password
        header("Location: reset_password.php");
        exit();
    } else {
        // Incorrect answer, redirect back with error
        header("Location: security_question_form.php?error=Incorrect answer");
        exit();
    }
} else {
    header("Location: forgot_password.php");
    exit();
}
?>