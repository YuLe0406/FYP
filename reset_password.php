<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_answer = $_POST['user_answer'];
    $correct_answer = $_SESSION['security_answer'];

    if (strcasecmp($user_answer, $correct_answer) == 0) {
        // Correct answer, show reset form
        echo "<form method='POST' action='update_password.php'>
                <label>New Password</label>
                <input type='password' name='new_password' required>
                <button type='submit'>Reset Password</button>
              </form>";
    } else {
        echo "Incorrect answer. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">CTRL+X</div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
        </nav>
    </header>

    <main class="auth-container">
        <form class="auth-form" action="update_password.php" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <div class="form-header">
                <h2 class="form-title">Set New Password</h2>
                <p class="form-subtitle">Enter a new password for your account</p>
            </div>

            <div class="input-group">
                <div class="input-field">
                    <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="input-field">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="auth-submit-btn">
                <i class="fas fa-sync"></i> Reset Password
            </button>
        </form>
    </main>
</body>
</html>