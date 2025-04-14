<?php

require __DIR__ . '/includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data with null coalescing
    $firstName = $_POST['first_name'] ?? '';
    $lastName  = $_POST['last_name'] ?? '';
    $email     = $_POST['email'] ?? '';
    $password  = $_POST['password'] ?? '';
    $phone     = $_POST['phone'] ?? '';

    // Validation checks
    $errors = [];
    
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName))  $errors[] = "Last name is required";
    if (empty($email))     $errors[] = "Email is required";
    if (empty($password))  $errors[] = "Password is required";
    if (empty($phone))     $errors[] = "Phone number is required";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!preg_match('/^[0-9]{11}$/', $phone)) {
        $errors[] = "Phone number must be 11 digits";
    }
    
    if (!empty($errors)) {
        die("Registration failed:<br>" . implode("<br>", $errors));
    }

    try {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO users 
            (first_name, last_name, email, password_hash, phone)
            VALUES (:first_name, :last_name, :email, :password, :phone)
        ");
        
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name'  => $lastName,
            ':email'      => $email,
            ':password'   => $passwordHash,
            ':phone'      => $phone
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
// 获取数据（带默认值）
$firstName = $_POST['first_name'] ?? '';
$lastName  = $_POST['last_name'] ?? '';
$email     = $_POST['email'] ?? '';
$password  = $_POST['password'] ?? '';
$phone     = $_POST['phone'] ?? '';
$dob       = $_POST['dob'] ?? '';
$gender    = $_POST['gender'] ?? '';

// 必填字段验证
$errors = [];
if (empty($firstName)) $errors[] = "First name is required";
if (empty($lastName))  $errors[] = "Last name is required";
if (empty($email))     $errors[] = "Email is required";
if (empty($password))  $errors[] = "Password is required";
if (empty($phone))     $errors[] = "Phone number is required";
if (empty($dob))       $errors[] = "Date of birth is required";
if (empty($gender))    $errors[] = "Gender is required";

// 邮箱格式验证
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

// 手机号格式验证
if (!preg_match('/^[0-9]{11}$/', $phone)) {
    $errors[] = "Phone number must be 11 digits";
}

// 如果有错误则终止
if (!empty($errors)) {
    die("Registration failed:<br>" . implode("<br>", $errors));
}