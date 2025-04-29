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
    
    // Password validation
    if ($new_password !== $confirm_password) {
        $error = "Passwords don't match";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[a-zA-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error = "Password must contain both letters and numbers";
    } else {
        // Update password in database (should use password_hash in production)
        $passwordHash = $new_password; // Replace with password_hash($new_password, PASSWORD_DEFAULT) for security
        $stmt = $conn->prepare("UPDATE USER SET U_Password = ? WHERE U_Email = ?");
        $stmt->bind_param("ss", $passwordHash, $_SESSION['reset_email']);
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
            $error = "Same with old password!";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-strength-meter {
            margin-top: 5px;
            width: 100%;
        }
        
        #strength-bar {
            width: 100%;
            height: 5px;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 3px;
        }
        
        #strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        #strength-text {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            color: #666;
        }
        
        .password-input-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        }
        
        .password-hint {
            display: block;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
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
                <div class="input-field password-field">
                    <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                    <div class="password-input-container">
                        <input type="password" id="new_password" name="new_password" minlength="8" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('new_password', 'confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength-meter">
                        <div id="strength-bar">
                            <div id="strength-fill"></div>
                        </div>
                        <div id="strength-text"></div>
                    </div>
                    <small class="password-hint">Password must be 8-20 letters and contain both letters and numbers. Allowed symbols: [#$%&(')'+,-/::<=>?@[|^_:{}]~</small>
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

    <script>
        function togglePasswordVisibility(passwordId, confirmPasswordId) {
            const password = document.getElementById(passwordId);
            const confirmPassword = document.getElementById(confirmPasswordId);
            const toggleBtn = document.querySelector('.toggle-password i');
            
            if (password.type === 'password') {
                password.type = 'text';
                confirmPassword.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                confirmPassword.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }

        // Password strength check (matches register.html exactly)
        document.getElementById('new_password').addEventListener('input', function() {
            const pwd = this.value;
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');

            const criteria = [
                pwd.length >= 8,                // Minimum length
                /[a-z]/.test(pwd),               // Lowercase letter
                /[A-Z]/.test(pwd),               // Uppercase letter
                /[0-9]/.test(pwd),              // Number
                /[#$%&(')'+,\-/:;<=>?@[\\\]^_`{|}~]/.test(pwd)  // Special char
            ];

            const strength = criteria.filter(Boolean).length;
            const colors = ["#e74c3c", "#e67e22", "#f1c40f", "#2ecc71", "#27ae60"];
            const levels = ["", "Very Weak", "Weak", "Medium", "Strong", "Very Strong"];
            
            strengthFill.style.width = (strength * 20) + "%";
            strengthFill.style.background = colors[strength - 1] || "#e0e0e0";
            strengthText.textContent = levels[strength] || "";
            strengthText.style.color = colors[strength - 1] || "#666";
        });

        // Form submission validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const pwd = document.getElementById('new_password').value;
            const confirmPwd = document.getElementById('confirm_password').value;

            // Check if passwords match
            if (pwd !== confirmPwd) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Passwords do not match!',
                    icon: 'error',
                    confirmButtonColor: '#e74c3c'
                });
                document.getElementById('confirm_password').focus();
                return;
            }

            // Check password requirements
            const isValid = pwd.length >= 8 && 
                           pwd.length <= 20 &&
                           /[a-zA-Z]/.test(pwd) && 
                           /[0-9]/.test(pwd);

            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Password must be 8-20 characters and contain both letters and numbers. Allowed symbols: [#$%&(\')\'+,-/::<=>?@[|^_:{}]~',
                    icon: 'error',
                    confirmButtonColor: '#e74c3c'
                });
            }
        });
    </script>
</body>
</html>