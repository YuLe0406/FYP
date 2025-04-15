<?php
require __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = strtolower($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $securityQuestion = $_POST['security_question'] ?? '';
    $securityAnswer = $_POST['security_answer'] ?? '';

    // Validation checks (unchanged)
    $errors = [];
    
    if (empty(trim($firstName))) $errors[] = "First name is required";
    // ... [Keep all validation checks the same] ...
    
    if (!empty($errors)) {
        die("Registration failed:<br>" . implode("<br>", $errors));
    }

    $passwordHash = $password; // Store password in plaintext
    
    // Insert into database using MySQLi
    $stmt = $conn->prepare("
        INSERT INTO users 
        (first_name, last_name, email, password_hash, phone, dob, gender, security_question, security_answer)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        die("Registration failed: " . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param(
        "sssssssss", // 9 strings
        $firstName,
        $lastName,
        $email,
        $passwordHash,
        $phone,
        $dob,
        $gender,
        $securityQuestion,
        $securityAnswer
    );
    
    // Execute query
    if (!$stmt->execute()) {
        if ($conn->errno == 1062) { // Duplicate email error code
            die("Registration failed: Email already exists");
        } else {
            error_log("Registration Error: " . $conn->error);
            die("System error. Please try later");
        }
    }
    
    $stmt->close();
    
    header("Location: login.html?registration=success");
    exit();
}