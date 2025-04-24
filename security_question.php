<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['U_Email'];

    // Check if the user exists
    $stmt = $conn->prepare("SELECT U_SecurityQuestion, U_SecurityAnswer FROM USER WHERE U_Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Save for later comparison
        $_SESSION['reset_email'] = $email;
        $_SESSION['security_answer'] = $user['U_SecurityAnswer'];
        
        // Redirect to the security question form
        header("Location: security_question_form.php");
        exit();
    } else {
        // Email not found, redirect back with error
        header("Location: forgot_password.php?error=Email not found");
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    // If not POST request, redirect to forgot password
    header("Location: forgot_password.php");
    exit();
}
?>