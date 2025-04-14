<?php
include 'db.php';
session_start();

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get admin details
$admin_details = [];
$sql = "SELECT A_Name, A_Email, A_CN FROM ADMIN WHERE A_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $admin_details = $result->fetch_assoc();
}
$stmt->close();

// Password change handling
$error_msg = '';
$success_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "All password fields are required!";
    } elseif (strlen($new_password) < 8) {
        $error_msg = "Password must be at least 8 characters!";
    } elseif (!preg_match("/[A-Z]/", $new_password) || !preg_match("/[0-9]/", $new_password)) {
        $error_msg = "Password must contain at least one uppercase letter and one number!";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New passwords don't match!";
    } else {
        // Verify current password
        $sql = "SELECT A_Password FROM ADMIN WHERE A_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current_password, $db_password)) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE ADMIN SET A_Password = ? WHERE A_ID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $admin_id);
            
            if ($update_stmt->execute()) {
                $success_msg = "Password updated successfully!";
                // Clear form on success
                $_POST = array();
            } else {
                $error_msg = "Error updating password: " . $conn->error;
            }
            $update_stmt->close();
        } else {
            $error_msg = "Current password is incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | CTRL-X</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        .container {
            display: flex;
            flex-direction: column;
            background-color: #f5f7fa;
            margin-left: 250px;
        }

        .main-content {
            width: 100%;
            max-width: 1000px;
            padding: 30px;
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background: var(--secondary-color);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .profile-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-section:last-child {
            border-bottom: none;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .password-strength {
            height: 5px;
            background: #e9ecef;
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            background: var(--danger-color);
            transition: width 0.3s, background 0.3s;
        }
        
        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="fas fa-user-cog me-2"></i>Admin Profile</h2>
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
            
            <div class="profile-body">
                <div class="profile-section">
                    <h4 class="mb-4"><i class="fas fa-user-circle me-2"></i>Profile Information</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Name:</strong></p>
                            <p><?php echo htmlspecialchars($admin_details['A_Name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Email:</strong></p>
                            <p><?php echo htmlspecialchars($admin_details['A_Email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Contact:</strong></p>
                            <p><?php echo htmlspecialchars($admin_details['A_CN'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="profile-section">
                    <h4 class="mb-4"><i class="fas fa-key me-2"></i>Change Password</h4>
                    
                    <?php if ($error_msg): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success_msg): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-12 password-toggle">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <i class="fas fa-eye" id="toggleCurrentPassword"></i>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12 password-toggle">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <i class="fas fa-eye" id="toggleNewPassword"></i>
                                <div class="password-strength mt-2">
                                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                </div>
                                <small class="text-muted">Minimum 8 characters with at least one uppercase letter and number</small>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12 password-toggle">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="update_password" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        document.querySelectorAll('.password-toggle i').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Change color based on strength
            if (strength < 50) {
                strengthBar.style.backgroundColor = 'var(--danger-color)';
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = 'var(--warning-color)';
            } else {
                strengthBar.style.backgroundColor = 'var(--success-color)';
            }
        });

        // Clear success message after 5 seconds
        <?php if ($success_msg): ?>
        setTimeout(() => {
            document.querySelector('.alert-success').style.display = 'none';
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>