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

    // Validation checks
    $errors = [];
    
    if (empty(trim($firstName))) $errors[] = "First name is required";
    if (empty(trim($lastName))) $errors[] = "Last name is required";
    if (empty(trim($email))) $errors[] = "Email is required";
    if (empty(trim($password))) $errors[] = "Password is required";
    if (empty(trim($confirmPassword))) $errors[] = "Confirm Password is required";
    if (empty(trim($phone))) $errors[] = "Phone number is required";
    if (empty(trim($dob))) $errors[] = "Date of birth is required";
    if (empty(trim($gender))) $errors[] = "Gender is required";
    if (empty(trim($securityQuestion))) $errors[] = "Security question is required";
    if (empty(trim($securityAnswer))) $errors[] = "Security answer is required";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!preg_match('/^[0-9]{11}$/', $phone)) {
        $errors[] = "Phone number must be 11 digits";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (!empty($errors)) {
        die("Registration failed:<br>" . implode("<br>", $errors));
    }

    try {
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $stmt = $conn->prepare("
           INSERT INTO users 
        (first_name, last_name, email, password_hash, phone, dob, gender, security_question, security_answer)
        VALUES (:first_name, :last_name, :email, :password, :phone, :dob, :gender, :security_question, :security_answer)
        
        ");
        
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password' => $passwordHash,
            ':phone' => $phone,
            ':dob' => $dob,
            ':gender' => $gender,
            ':security_question' => $securityQuestion,
            ':security_answer' => $securityAnswer
        ]);
        
        header("Location: login.html?registration=success");
        exit();
        
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            die("Registration failed: Email already exists");
        } else {
            error_log("Registration Error: " . $e->getMessage());
            die("System error. Please try later");
        }
    }
}