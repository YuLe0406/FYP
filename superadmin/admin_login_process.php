<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['A_Email'];
    $password = $_POST['A_Password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT A_ID, A_Name, A_Password, A_Level, A_Status FROM ADMIN WHERE A_Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // Check if account is blocked
        if ((int)$admin['A_Status'] === 1) {
            header("Location: admin_login.php?error=2"); // Account blocked
            exit();
        }

        // Secure password verification
        if (password_verify($password, $admin['A_Password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['A_ID'];
            $_SESSION['admin_name'] = $admin['A_Name'];
            $_SESSION['admin_level'] = $admin['A_Level'];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        }
    }

    // If login fails
    header("Location: admin_login.php?error=1");
    exit();
} else {
    header("Location: admin_login.php");
    exit();
}
