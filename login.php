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
        header("Location: login.php");
        exit();
    }

    // Validate password format
    if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).{8,}$/', $password)) {
        $_SESSION['login_error'] = "Invalid password format. Password must contain at least 8 characters with both letters and numbers.";
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM USER WHERE U_Email = ?");
    if (!$stmt) {
        $_SESSION['login_error'] = "System error. Please try later.";
        header("Location: login.php");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($password === $user['U_Password']) {
            $_SESSION['user_id'] = $user['U_ID'];
            $_SESSION['user_email'] = $user['U_Email'];
            $_SESSION['user_name'] = $user['U_FName'] . ' ' . $user['U_LName'];
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid password!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "This email is not registered. Please check your email or <a href='register.html'>create an account</a>.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}

// Read the HTML file
$html = file_get_contents('login.html');

// Inject alert script if there's an error
if (isset($_SESSION['login_error'])) {
    $error = htmlspecialchars($_SESSION['login_error']);
    $alertScript = "
        <script>
            Swal.fire({
                title: 'Login Error',
                html: `{$error}`,
                icon: 'error',
                confirmButtonColor: '#4CAF50'
            });
        </script>
    ";
    $html = str_replace('<!-- ALERT_SCRIPT_PLACEHOLDER -->', $alertScript, $html);
    unset($_SESSION['login_error']);
} else {
    $html = str_replace('<!-- ALERT_SCRIPT_PLACEHOLDER -->', '', $html);
}

echo $html;
?>