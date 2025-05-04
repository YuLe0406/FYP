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

    // Basic field validation
    if (empty(trim($firstName))) $errors[] = "First name is required";
    if (empty(trim($lastName))) $errors[] = "Last name is required";
    if (empty(trim($email))) $errors[] = "Email is required";
    if (empty(trim($password))) $errors[] = "Password is required";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match";
    if (empty(trim($phone))) $errors[] = "Phone number is required";
    if (!preg_match('/^[0-9]{10,11}$/', $phone)) $errors[] = "Phone number must be 10 or 11 digits";
    if (empty(trim($gender))) $errors[] = "Gender is required";
    if (empty(trim($securityQuestion))) $errors[] = "Security question is required";
    if (empty(trim($securityAnswer))) $errors[] = "Security answer is required";

    // Date of birth validation
    if (empty(trim($dob))) {
        $errors[] = "Date of birth is required";
    } else {
        try {
            $dobDate = new DateTime($dob);
            $today = new DateTime();
            
            // Check if date is in the future
            if ($dobDate > $today) {
                $errors[] = "Date of birth cannot be in the future";
            } else {
                // Calculate age
                $age = $today->diff($dobDate)->y;
                
                // Check if user is at least 18 years old
                if ($age < 18) {
                    $errors[] = "You must be at least 18 years old to register";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Invalid date format";
        }
    }

    // If there are errors, display them and stop execution
    if (!empty($errors)) {
        die("Registration failed:<br>" . implode("<br>", $errors));
    }

    // Hash the password (consider using password_hash() for better security)
    $passwordHash = $password; // Replace with password_hash($password, PASSWORD_DEFAULT) in production

    // Prepare SQL statement with new column names
    $stmt = $conn->prepare("
        INSERT INTO USER 
        (U_FName, U_LName, U_Email, U_Password, U_PNumber, U_DOB, U_Gender, U_SecurityQuestion, U_SecurityAnswer)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Registration failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "sssssssss",
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

    // Execute the statement
    if (!$stmt->execute()) {
        if ($conn->errno == 1062) {
            die("Registration failed: Email already exists");
        } else {
            error_log("Registration Error: " . $conn->error);
            die("System error. Please try later");
        }
    }

    // Close statement and redirect
    $stmt->close();
    header("Location: login.html?registration=success");
    exit();
}