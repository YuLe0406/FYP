<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];
    if (empty(trim($email)))    $errors[] = "Email is required";
    if (empty(trim($password))) $errors[] = "Password is required";

    if (!empty($errors)) {
        $_SESSION['login_error'] = implode("<br>", $errors);
        header("Location: login.html");
        exit();
    }

    // Validate password format (matches register requirements)
    if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).{8,}$/', $password)) {
        $_SESSION['login_error'] = "Invalid password format. Password must contain at least 8 characters with both letters and numbers.";
        header("Location: login.html");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM USER WHERE U_Email = ?");
    if (!$stmt) {
        $_SESSION['login_error'] = "System error. Please try later.";
        header("Location: login.html");
        exit();
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
            $_SESSION['login_error'] = "Invalid email or password!";
            header("Location: login.html");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password!";
        header("Location: login.html");
        exit();
    }

    $stmt->close();
}
?>