<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data for password verification
$stmt = $conn->prepare("SELECT U_Password FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$error = '';
$success = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Verify current password (in production, use password_verify())
    if ($currentPassword !== $user['U_Password']) {
        $error = "Current password is incorrect";
    } elseif (empty(trim($newPassword))) {
        $error = "New password is required";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match";
    } elseif (strlen($newPassword) < 8 || strlen($newPassword) > 20) {
        $error = "Password must be 8-20 characters";
    } elseif (!preg_match('/[a-zA-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $error = "Password must contain both letters and numbers";
    } else {
        // Update password (in production, use password_hash())
        $update_stmt = $conn->prepare("UPDATE USER SET U_Password = ? WHERE U_ID = ?");
        $update_stmt->bind_param("si", $newPassword, $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Failed to update password: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | CTRL+X</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
        }
        .sidebar a.active {
            background: #3498db;
            border-left: 4px solid #2980b9;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .password-form {
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
        }
        .password-strength {
            margin-top: 5px;
            font-size: 0.8em;
        }
        .password-strength.weak {
            color: #e74c3c;
        }
        .password-strength.medium {
            color: #f39c12;
        }
        .password-strength.strong {
            color: #27ae60;
        }
        .password-hint {
            display: block;
            margin-top: 5px;
            font-size: 0.8em;
            color: #7f8c8d;
        }
        .form-actions {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
            <a href="addresses.php"><i class="fas fa-map-marker-alt"></i> My Addresses</a>
            <a href="change_password.php" class="active"><i class="fas fa-key"></i> Change Password</a>
            <a href="cart.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
            <a href="#"><i class="fas fa-tag"></i> My Coupons</a>
            <a href="wishlist.php"><i class="fas fa-heart"></i> My Wishlist</a>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <h2 class="section-title">CHANGE PASSWORD</h2>

            <form method="POST" class="password-form">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="password-input-container">
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('current_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-input-container">
                        <input type="password" id="new_password" name="new_password" required minlength="8" maxlength="20">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('new_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                    <small class="password-hint">Password must be 8-20 characters and contain both letters and numbers. Allowed symbols: [#$%&(')'+,-/::<=>?@[|^_:{}]~</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-input-container">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="20">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <script>
        Swal.fire({
            title: 'Error!',
            text: '<?= $error ?>',
            icon: 'error',
            confirmButtonColor: '#3498db'
        });
        </script>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <script>
        Swal.fire({
            title: 'Success!',
            text: '<?= $success ?>',
            icon: 'success',
            confirmButtonColor: '#3498db'
        }).then(() => {
            window.location.href = 'profile.php';
        });
        </script>
    <?php endif; ?>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const strengthIndicator = document.getElementById('passwordStrength');
            const password = this.value;
            
            // Define strength criteria
            const hasMinLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChars = /[#$%&(')'+,\-/:;<=>?@[\\\]^_`{|}~]/.test(password);
            
            // Calculate strength score
            let score = 0;
            if (hasMinLength) score++;
            if (hasUpperCase) score++;
            if (hasLowerCase) score++;
            if (hasNumbers) score++;
            if (hasSpecialChars) score++;
            
            // Determine strength level
            if (password.length === 0) {
                strengthIndicator.textContent = '';
                strengthIndicator.className = 'password-strength';
            } else if (password.length < 8) {
                strengthIndicator.textContent = 'Password too short (min 8 characters)';
                strengthIndicator.className = 'password-strength weak';
            } else if (score <= 2) {
                strengthIndicator.textContent = 'Weak password';
                strengthIndicator.className = 'password-strength weak';
            } else if (score <= 3) {
                strengthIndicator.textContent = 'Medium password';
                strengthIndicator.className = 'password-strength medium';
            } else if (score <= 4) {
                strengthIndicator.textContent = 'Strong password';
                strengthIndicator.className = 'password-strength strong';
            } else {
                strengthIndicator.textContent = 'Very strong password';
                strengthIndicator.className = 'password-strength strong';
            }
        });

        // Form validation
        document.querySelector('.password-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check if passwords match
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error!',
                    text: 'New passwords do not match!',
                    icon: 'error',
                    confirmButtonColor: '#3498db'
                });
                return;
            }
            
            // Check password requirements
            const isValid = newPassword.length >= 8 && 
                          newPassword.length <= 20 &&
                          /[a-zA-Z]/.test(newPassword) && 
                          /[0-9]/.test(newPassword);

            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error!',
                    text: 'Password must be 8-20 characters and contain both letters and numbers. Allowed symbols: [#$%&(\')\'+,-/::<=>?@[|^_:{}]~',
                    icon: 'error',
                    confirmButtonColor: '#3498db'
                });
            }
        });
    </script>
</body>
</html>