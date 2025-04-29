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
    $address = $_POST['address'] ?? '';
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
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match";
    if (empty(trim($phone))) $errors[] = "Phone number is required";
    if (empty(trim($address))) $errors[] = "Address is required";
    if (empty(trim($dob))) $errors[] = "Date of birth is required";
    if (empty(trim($gender))) $errors[] = "Gender is required";
    if (empty(trim($securityQuestion))) $errors[] = "Security question is required";
    if (empty(trim($securityAnswer))) $errors[] = "Security answer is required";

    if (!empty($errors)) {
        die("Registration failed:<br>" . implode("<br>", $errors));
    }

    $passwordHash = $password; // (Use password_hash later for better security)

    // Updated SQL with new column names
    $stmt = $conn->prepare("
        INSERT INTO USER 
        (U_FName, U_LName, U_Email, U_Password, U_PNumber, U_Address ,U_DOB, U_Gender, U_SecurityQuestion, U_SecurityAnswer)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Registration failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssssssss", // 10 strings
        $firstName,
        $lastName,
        $email,
        $passwordHash,
        $phone,
        $address,
        $dob,
        $gender,
        $securityQuestion,
        $securityAnswer
    );

    if (!$stmt->execute()) {
        if ($conn->errno == 1062) {
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
