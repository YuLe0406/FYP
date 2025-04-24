<?php
session_start();
require __DIR__ . '/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];
    if (empty(trim($email)))    $errors[] = "Email is required";
    if (empty(trim($password))) $errors[] = "Password is required";

    if (!empty($errors)) {
        die("Login failed:<br>" . implode("<br>", $errors));
    }

    // Validate password format (client-side should catch this, but good to double-check)
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        die("Invalid password format. Password must contain at least 8 characters, including uppercase, lowercase, number and special character.");
    }

    $stmt = $conn->prepare("SELECT * FROM USER WHERE U_Email = ?");
    if (!$stmt) {
        die("Error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Verify password (plain text comparison as per your request)
        if ($password === $user['U_Password']) {
            $_SESSION['user_id'] = $user['U_ID'];
            $_SESSION['user_email'] = $user['U_Email'];
            $_SESSION['user_name'] = $user['U_FName'] . ' ' . $user['U_LName'];
            header("Location: index.php");
            exit();
        } else {
            die("Invalid email or password!");
        }
    } else {
        die("Invalid email or password!");
    }

    $stmt->close();
}
?>