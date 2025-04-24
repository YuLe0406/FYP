<?php
session_start();

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

// Check if form is submitted to update password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'db.php';
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $error = '';
    
    if ($new_password !== $confirm_password) {
        $error = "Passwords don't match";
    } else {
        // Update password in database (without hashing)
        $stmt = $conn->prepare("UPDATE USER SET U_Password = ? WHERE U_Email = ?");
        $stmt->bind_param("ss", $new_password, $_SESSION['reset_email']);
        $stmt->execute();
        
        if ($stmt->affected_rows === 1) {
            // Password updated successfully
            session_destroy();
            // Show styled success alert
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Password Reset Success</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <link rel="stylesheet" href="auth.css">
            </head>
            <body>
                <script>
                Swal.fire({
                    title: "Success!",
                    text: "Password reset successfully!",
                    icon: "success",
                    confirmButtonColor: "#4CAF50",
                    confirmButtonText: "Continue to Login",
                    backdrop: `
                        rgba(0,0,123,0.4)
                        url("/images/nyan-cat.gif")
                        left top
                        no-repeat
                    `
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "login.html";
                    }
                });
                </script>
            </body>
            </html>';
            exit();
        } else {
            $error = "Failed to update password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | CTRL+X</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <header>
        <div class="logo">CTRL+X</div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
        </nav>
    </header>

    <main class="auth-container">
        <form class="auth-form" method="POST">
            <div class="form-header">
                <h2 class="form-title">Set New Password</h2>
                <p class="form-subtitle">Enter a new password for your account</p>
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
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