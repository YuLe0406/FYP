<?php
session_start(); // Add this at the top
require_once 'db.php';

// Initialize variables
$email = '';
$error = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['U_Email']);
    
    // Validate email
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists in database
        $query = "SELECT U_ID, U_Email FROM USER WHERE U_Email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Email not found in our system';
        } else {
            // Store email in session for security question page
            $_SESSION['reset_email'] = $email;
            
            // Redirect to security question page
            header("Location: security_question.php?email=".urlencode($email));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | CTRL+X</title>
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
        <form class="auth-form" method="POST">
            <div class="form-header">
                <h2 class="form-title">Reset Password</h2>
                <p class="form-subtitle">Enter your registered email</p>
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <div class="input-field">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="U_Email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
            </div>

            <button type="submit" class="auth-submit-btn">
                <i class="fas fa-key"></i> Continue
            </button>

            <div class="form-footer">
                <p>Remember your password? <a href="login.html" class="auth-link">Login here</a></p>
            </div>
        </form>
    </main>
</body>
</html>