<?php
session_start();
require 'db.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user's answer from the form
    $user_answer = trim($_POST['security_answer']);
    
    // Fetch the correct answer from database
    $stmt = $conn->prepare("SELECT U_SecurityAnswer FROM USER WHERE U_Email = ?");
    $stmt->bind_param("s", $_SESSION['reset_email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("User not found");
    }
    
    $user = $result->fetch_assoc();
    $correct_answer = $user['U_SecurityAnswer'];
    $stmt->close();
    
    // Compare answers (case-insensitive)
    if (strcasecmp($user_answer, $correct_answer) === 0) {
        // Correct answer, redirect to reset password
        header("Location: reset_password.php");
        exit();
    } else {
        // Incorrect answer, store error in session and redirect back
        $_SESSION['security_answer_error'] = "Incorrect answer. Please try again.";
        header("Location: security_question_form.php");
        exit();
    }
} else {
    header("Location: forgot_password.php");
    exit();
}
?>